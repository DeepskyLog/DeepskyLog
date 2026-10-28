<?php

namespace App\Console\Commands;

use App\Mail\MessageReceived;
use App\Models\GreatRedSpotPosition;
use App\Models\Message;
use App\Models\Team;
use App\Models\User;
use App\Services\Planets\PlanetMoonEvents;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Asks the administrators and database experts every month to check the
 * System II longitude of Jupiter's Great Red Spot (GreatRedSpotPosition) on
 * /admin/great-red-spot: the spot drifts irregularly, and no source gives its
 * longitude in a form DeepskyLog can read. One DeepskyLog message each (mail
 * if sendMail) with the longitude assumed today.
 */
class SendGreatRedSpotReminder extends Command
{
    protected $signature = 'planets:grs-reminder
        {--apply : Send the messages}';

    protected $description = 'Ask the administrators and database experts to check the longitude of the Great Red Spot';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to send the messages.');
        }

        $current    = GreatRedSpotPosition::current();
        $longitude  = PlanetMoonEvents::grsLongitude(now()->getTimestamp());
        $recipients = User::query()
            ->whereHas('teams', fn ($q) => $q->whereIn('teams.id', [Team::ADMINISTRATORS, Team::DATABASE_EXPERTS]))
            ->get();

        foreach ($recipients as $recipient) {
            if (! $apply) {
                continue;
            }
            try {
                $locale  = $recipient->language ?: config('app.locale');
                $subject = __('Check the longitude of the Great Red Spot', [], $locale);
                $body    = Message::sanitizeHtml('<p>' . e(__('DeepskyLog assumes the Great Red Spot of Jupiter at System II longitude :longitude° today (:start° on :date, drifting :drift° a year). Please compare it with the latest measurements of JUPOS or the value Sky & Telescope uses, and enter a new value if it differs:', [
                    'longitude' => number_format($longitude, 1),
                    'start'     => number_format($current['longitude'], 1),
                    'date'      => Carbon::parse($current['measured_on'])->locale($locale)->isoFormat('LL'),
                    'drift'     => number_format($current['drift'], 1),
                ], $locale)) . '</p><p><a href="' . e(route('admin.great-red-spot')) . '">' . e(__('Longitude of the Great Red Spot', [], $locale)) . '</a></p>');

                $message = Message::create([
                    'sender'   => 'deepskylog',
                    'receiver' => $recipient->username,
                    'subject'  => $subject,
                    'message'  => $body,
                    'date'     => now(),
                ]);

                if ($recipient->sendMail && $recipient->email) {
                    Mail::to($recipient->email)
                        ->locale($locale)
                        ->send(new MessageReceived('DeepskyLog', $subject, $body, $recipient->name, route('messages.show', $message->id)));
                }
            } catch (\Throwable $e) {
                logger()->error('Failed to remind ' . $recipient->username . ' of the Great Red Spot: ' . $e->getMessage());
            }
        }

        $this->info('Longitude today: ' . number_format($longitude, 1) . '°');
        $this->info(($apply ? 'Sent: ' : 'To send: ') . $recipients->count());

        return self::SUCCESS;
    }
}
