<?php

namespace App\Services;

use App\Jobs\NotifyObservingRequestAnswer;
use App\Mail\MessageReceived;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\Message;
use App\Models\Observation;
use App\Models\ObservingRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Observing requests (App\Models\ObservingRequest) at work: the answers that
 * new observations give, the message to the observer who asked, and which
 * open requests an observer's standard telescope can probably answer.
 */
class ObservingRequests
{
    /** The requests shown on the home page. */
    public const HOMEPAGE = 3;

    /** A request the telescope shows ('visible'), whose contrast reserve is still being calculated ('pending'), or for which the observer has no standard equipment (null). */
    public const VERDICTS = ['visible', 'pending'];

    /**
     * Record the observation as an answer of the open requests of its
     * object it answers, and queue the message to the observers who asked
     * (queued, as the visibility of the observation is saved after it).
     * Called by ObservationObserver::created().
     */
    public static function observationCreated(Observation $observation): void
    {
        if ($observation->target_type !== Observation::TYPE_DEEPSKY || ! $observation->target_name) {
            return;
        }
        $requests = ObservingRequest::open()->where('object_name', $observation->target_name)->get();
        if ($requests->isEmpty()) {
            return;
        }

        $aperture = $observation->instrument_id ? Instrument::whereKey($observation->instrument_id)->value('aperture_mm') : null;
        $observerId = $observation->observer_user_id
            ?: User::where('username', $observation->observer_username)->value('id');
        $observation->observer_user_id = $observerId;

        foreach ($requests as $request) {
            if (! $request->isAnsweredBy($observation, is_numeric($aperture) ? (float) $aperture : null)) {
                continue;
            }
            $inserted = DB::table('observing_request_answers')->insertOrIgnore([
                'observing_request_id' => $request->id,
                'observation_id' => $observation->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($inserted === 0) {
                continue;
            }
            if ($request->answered_at === null) {
                $request->forceFill(['answered_at' => now()])->save();
            }
            NotifyObservingRequestAnswer::dispatch($request->id, (int) $observation->id)
                ->delay(now()->addSeconds(NotifyObservingRequestAnswer::DELAY_SECONDS))
                ->afterCommit();
        }
    }

    /**
     * Tell the observer who asked that an observation answers their request:
     * a DeepskyLog message in their language (mail if sendMail) and a push
     * notification.
     */
    public static function notify(ObservingRequest $request, Observation $observation): void
    {
        $recipient = $request->user;
        if (! $recipient) {
            return;
        }
        $observer = User::where('username', $observation->observer_username)->first();
        $locale = $recipient->language ?: config('app.locale');
        $seen = self::seen($observation);
        $aperture = $observation->instrument_id ? Instrument::whereKey($observation->instrument_id)->value('aperture_mm') : null;
        $name = $request->object_name;
        $observerName = $observer?->name ?? (string) $observation->observer_username;

        $subject = __('Your observing request for :name was answered', ['name' => $name], $locale);
        $line = match ($seen) {
            true => __(':observer saw :name', ['observer' => $observerName, 'name' => $name], $locale),
            false => __(':observer looked for :name but did not see it', ['observer' => $observerName, 'name' => $name], $locale),
            default => __(':observer observed :name', ['observer' => $observerName, 'name' => $name], $locale),
        };
        if (is_numeric($aperture) && $aperture > 0) {
            $line .= ' ' . __('with :aperture', ['aperture' => ObservingRequest::apertureLabel((int) round($aperture), $recipient)], $locale);
        }
        $url = $observation->detailUrl($observer?->slug) ?? route('observing-requests');

        try {
            $body = Message::sanitizeHtml(
                '<p>' . e($line . '.') . '</p>'
                . ($request->note ? '<blockquote>' . e($request->note) . '</blockquote>' : '')
                . '<p><a href="' . e($url) . '">' . e(__('Read the observation', [], $locale)) . '</a>'
                . ' · <a href="' . e(route('observing-requests', ['view' => 'mine'])) . '">' . e(__('My observing requests', [], $locale)) . '</a></p>'
            );
            $message = Message::create([
                'sender' => 'deepskylog',
                'receiver' => $recipient->username,
                'subject' => $subject,
                'message' => $body,
                'date' => now(),
            ]);
            if ($recipient->sendMail && $recipient->email) {
                Mail::to($recipient->email)
                    ->locale($locale)
                    ->send(new MessageReceived('DeepskyLog', $subject, $body, $recipient->name, route('messages.show', $message->id)));
            }
            PushNotifications::send($recipient, $subject, $line . '.', $url, 'observing-request-' . $request->id);
        } catch (\Throwable $e) {
            logger()->error('Failed to tell ' . $recipient->username . ' of an answer to the observing request for ' . $name . ': ' . $e->getMessage());
        }
    }

    /** Whether the observation saw its object: null when it has no visibility (yet). */
    public static function seen(Observation $observation): ?bool
    {
        $visibility = DB::table('deepsky_observation_details')->where('observation_id', $observation->id)->value('visibility');
        if ($visibility === null) {
            return null;
        }

        return (int) $visibility !== Observation::VISIBILITY_NOT_SEEN;
    }

    /**
     * The open requests of other observers that $viewer can answer: none of
     * their observations answers it yet, and with a standard telescope and
     * location, that telescope is large enough and shows the object (its
     * stored contrast reserve above EquipmentGain::VISIBLE_ABOVE; a missing
     * one is queued and the request kept as 'pending'). Each request gets
     * `verdict` (null without standard equipment) and `contrast_reserve`.
     * The newest first.
     *
     * @return Collection<int, ObservingRequest>
     */
    public static function forViewer(User $viewer): Collection
    {
        $requests = ObservingRequest::open()
            ->where('user_id', '!=', $viewer->id)
            ->whereDoesntHave('answers', fn ($q) => $q->where('observations.observer_username', $viewer->username))
            ->with('user:id,name,slug,username')
            ->withCount('answers')
            ->orderByDesc('created_at')
            ->get();

        $instrument = $viewer->stdtelescope ? Instrument::where('user_id', $viewer->id)->find($viewer->stdtelescope) : null;
        $location = $viewer->stdlocation ? Location::find($viewer->stdlocation) : null;
        if (! $instrument || ! $location || $requests->isEmpty()) {
            return $requests->each(fn ($r) => $r->setAttribute('verdict', null));
        }

        $aperture = (float) $instrument->aperture_mm;
        $requests = $requests->filter(fn ($r) => $r->min_aperture_mm === null || $aperture >= $r->min_aperture_mm)->values();

        $lensId = $viewer->standardLensId();
        $names = $requests->pluck('object_name')->unique()->values()->all();
        $metrics = DB::table('user_object_metrics')
            ->where('user_id', $viewer->id)
            ->where('instrument_id', $instrument->id)
            ->where('location_id', $location->id)
            ->when($lensId === null, fn ($q) => $q->whereNull('lens_id'), fn ($q) => $q->where('lens_id', $lensId))
            ->whereIn('object_name', $names)
            ->get(['object_name', 'contrast_reserve'])
            ->keyBy(fn ($row) => mb_strtolower((string) $row->object_name));
        $types = DB::table('objects')->whereIn('name', $names)->pluck('type', 'name')->mapWithKeys(fn ($type, $name) => [mb_strtolower($name) => $type]);

        $missing = [];
        $kept = $requests->filter(function (ObservingRequest $request) use ($metrics, $types, &$missing) {
            $key = mb_strtolower($request->object_name);
            if (! ContrastReserveCalculator::appliesTo($types[$key] ?? null)) {
                // a double star has no contrast reserve: the aperture asked for decides
                $request->setAttribute('verdict', 'visible');

                return true;
            }
            $metric = $metrics[$key] ?? null;
            if (! $metric) {
                $missing[] = $request->object_name;
                $request->setAttribute('verdict', 'pending');

                return true;
            }
            if (! is_numeric($metric->contrast_reserve) || (float) $metric->contrast_reserve <= EquipmentGain::VISIBLE_ABOVE) {
                return false;
            }
            $request->setAttribute('verdict', 'visible');
            $request->setAttribute('contrast_reserve', round((float) $metric->contrast_reserve, 2));

            return true;
        })->values();

        if ($missing !== []) {
            ObjectMetricsQueue::queueMissing($viewer->id, $instrument->id, $location->id, $lensId, $missing);
        }

        return $kept;
    }

    /**
     * The home page box: the number of requests $viewer can answer and the
     * newest HOMEPAGE of them (with the `slug` of their object); null when
     * there are none.
     *
     * @return array{count: int, requests: Collection<int, ObservingRequest>}|null
     */
    public static function forHomePage(User $viewer): ?array
    {
        $requests = self::forViewer($viewer);
        if ($requests->isEmpty()) {
            return null;
        }

        $shown = $requests->take(self::HOMEPAGE)->values();
        $slugs = DB::table('objects')->whereIn('name', $shown->pluck('object_name')->all())->pluck('slug', 'name');
        $shown->each(fn (ObservingRequest $request) => $request->setAttribute('slug', $slugs[$request->object_name] ?? null));

        return ['count' => $requests->count(), 'requests' => $shown];
    }

    /**
     * The names (lower case) among $names with an open request of another
     * observer than $viewer, to mark them in Tonight's best and Next for you.
     *
     * @param  iterable<string>  $names
     * @return array<string, true>
     */
    public static function requestedAmong(iterable $names, ?User $viewer): array
    {
        $names = collect($names)->filter()->unique()->values();
        if ($names->isEmpty()) {
            return [];
        }

        return $names->chunk(1000)
            ->flatMap(fn ($chunk) => ObservingRequest::open()
                ->whereIn('object_name', $chunk->all())
                ->when($viewer, fn ($q) => $q->where('user_id', '!=', $viewer->id))
                ->pluck('object_name'))
            ->mapWithKeys(fn ($name) => [mb_strtolower($name) => true])
            ->all();
    }
}
