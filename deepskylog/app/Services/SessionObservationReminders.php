<?php

namespace App\Services;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The sessions whose observer has not recorded any observation in them yet:
 * a nudge to finish logging the night. The command
 * sessions:send-observation-reminders sends the observer one message for such
 * a session, REMIND_AFTER_HOURS after it ended (at most MAX_DAYS later, so
 * the old sessions without observations are never reminded); the home page
 * and the session page show them to the observer as well.
 *
 * A session holds the observations of the sessionObservations pivot
 * (SessionObservationLinker); only those of the session's own observer count,
 * not those of the observers who came along.
 */
class SessionObservationReminders
{
    /** A session is reminded this many hours after it ended, so the observer can log the night first. */
    public const REMIND_AFTER_HOURS = 24;

    /** Sessions that ended longer ago are not reminded. */
    public const MAX_DAYS = 14;

    /** The home page shows the sessions of this many days. */
    public const HOMEPAGE_DAYS = 30;

    /** The home page shows at most this many sessions. */
    public const HOMEPAGE_SESSIONS = 5;

    /**
     * The active sessions that ended (the end date, else the begin date)
     * between the given moments and have no observation of their observer.
     */
    public static function query(\DateTimeInterface $endedAfter, \DateTimeInterface $endedBefore): Builder
    {
        $end = DB::raw('COALESCE(observation_sessions.enddate, observation_sessions.begindate)');

        return ObservationSession::query()
            ->where('active', 1)
            ->where(fn (Builder $q) => $q->whereNotNull('begindate')->orWhereNotNull('enddate'))
            ->where($end, '>=', $endedAfter->format('Y-m-d H:i:s'))
            ->where($end, '<', $endedBefore->format('Y-m-d H:i:s'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
                ->from('sessionObservations')
                ->join('observations', 'observations.id', '=', 'sessionObservations.observationid')
                ->whereColumn('sessionObservations.sessionid', 'observation_sessions.id')
                ->whereColumn('observations.observer_username', 'observation_sessions.observerid'));
    }

    /**
     * The sessions to remind now: ended REMIND_AFTER_HOURS to MAX_DAYS ago,
     * not reminded yet, of observers who did not turn the reminder off.
     */
    public static function due(): Builder
    {
        return self::query(now()->subDays(self::MAX_DAYS), now()->subHours(self::REMIND_AFTER_HOURS))
            ->whereNull('observation_reminder_sent_at')
            ->whereIn('observerid', User::where('session_reminder', true)->select('username'));
    }

    /**
     * The observer's own sessions of the last HOMEPAGE_DAYS that have no
     * observation of theirs, newest first (also those that are not due yet:
     * the observer may log them right away).
     *
     * @return Collection<int, ObservationSession>
     */
    public static function forUser(User $user, int $limit = self::HOMEPAGE_SESSIONS): Collection
    {
        return self::query(now()->subDays(self::HOMEPAGE_DAYS), now())
            ->where('observerid', $user->username)
            ->orderByDesc(DB::raw('COALESCE(observation_sessions.enddate, observation_sessions.begindate)'))
            ->limit($limit)
            ->get();
    }

    /**
     * Whether the observer of the session has recorded no observation in it,
     * once it has started.
     */
    public static function lacksObservations(ObservationSession $session): bool
    {
        $start = $session->begindate ?? $session->enddate;
        if ($start === null || $start->isFuture()) {
            return false;
        }

        return ! DB::table('sessionObservations')
            ->join('observations', 'observations.id', '=', 'sessionObservations.observationid')
            ->where('sessionObservations.sessionid', $session->id)
            ->where('observations.observer_username', $session->observerid)
            ->exists();
    }

    /**
     * The form of a new observation filled in with the date and location of the session.
     */
    public static function addObservationUrl(ObservationSession $session): string
    {
        return route('observation.create', ['session' => $session->id]);
    }

    /**
     * Send the observer one message (and a mail if they asked for mails)
     * about their sessions without observations.
     *
     * @param  Collection<int, ObservationSession>  $sessions
     */
    public static function send(User $user, Collection $sessions): bool
    {
        try {
            $locale = $user->language ?: config('app.locale');

            $subject = trans_choice(
                'Your session has no observations yet|Your sessions have no observations yet',
                $sessions->count(),
                [],
                $locale,
            );

            $body = '<p>'.e(trans_choice(
                'You have not added any observations to this session yet. Add what you saw while you still remember the night!|You have not added any observations to these sessions yet. Add what you saw while you still remember the nights!',
                $sessions->count(),
                [],
                $locale,
            )).'</p><ul>';
            foreach ($sessions as $session) {
                $name = e(html_entity_decode((string) $session->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $date = ($session->begindate ?? $session->enddate)?->locale($locale)->isoFormat('LL');
                $link = route('session.show', [$user->slug, $session->slug ?: $session->id]);
                $body .= '<li><a href="'.e($link).'">'.$name.'</a>'.($date ? ' ('.e($date).')' : '')
                    .': <a href="'.e(self::addObservationUrl($session)).'">'.e(__('Add observations', [], $locale)).'</a></li>';
            }
            $body .= '</ul><p>'.e(__('You can turn this message off in your profile settings.', [], $locale)).'</p>';
            $body = Message::sanitizeHtml($body);

            $message = Message::create([
                'sender' => 'deepskylog',
                'receiver' => $user->username,
                'subject' => Message::sanitizeHtml($subject),
                'message' => $body,
                'date' => now(),
            ]);

            if ($user->sendMail) {
                Mail::to($user->email)
                    ->locale($locale)
                    ->send(new MessageReceived('DeepskyLog', $subject, $body, $user->name, route('messages.show', $message->id)));
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Failed to send the session observation reminder', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
