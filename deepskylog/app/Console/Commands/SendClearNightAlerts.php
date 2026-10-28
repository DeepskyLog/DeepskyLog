<?php

namespace App\Console\Commands;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\User;
use App\Services\NightWindow;
use App\Services\PushNotifications;
use App\Services\Weather\NightForecast;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the observers who asked for it on the profile page
 * (users.clear_night_alert) a DeepskyLog message when the coming night at
 * their standard location is forecast to be clear and dark (NightForecast's
 * verdict clear_dark), with a link to make an observing list for it, and a
 * push notification on the devices where they turned those on.
 *
 * An observer is looked at from ALERT_HOUR local time at that location until
 * it gets dark, so a forecast that clears up in the afternoon still gives a
 * message; users.clear_night_alert_night is the night the last message was
 * for, so a night gets at most one. Scheduled hourly with --apply.
 */
class SendClearNightAlerts extends Command
{
    /** The local hour from which the forecast of the coming night is trusted. */
    public const ALERT_HOUR = 12;

    protected $signature = 'forecast:send-clear-night-alerts
        {--apply : Send the messages}
        {--any-time : Also look at observers before noon or after dark at their location}';

    protected $description = 'Send a message to the observers whose standard location is forecast to have a clear, dark night';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to send the messages.');
        }
        if (! NightForecast::enabled()) {
            $this->warn('The forecast is turned off (WEATHER_FORECAST=false).');

            return self::SUCCESS;
        }

        $sent = 0;
        $rows = [];

        User::query()
            ->where('clear_night_alert', true)
            ->whereNotNull('stdlocation')
            ->with('standardLocation')
            ->orderBy('id')
            ->each(function (User $user) use ($apply, &$sent, &$rows) {
                $location = $user->standardLocation;
                if (! $location || ! $location->hasValidCoordinates()) {
                    return;
                }

                $timezone = EphemerisDate::timezone($location);
                $night    = NightForecast::firstNight((float) $location->latitude, (float) $location->longitude, $timezone);
                if ($user->clear_night_alert_night?->toDateString() === $night) {
                    return;
                }

                if (! $this->option('any-time')) {
                    $window = NightWindow::for((float) $location->latitude, (float) $location->longitude, $night, $timezone);
                    if ($window === null || Carbon::now($timezone)->hour < self::ALERT_HOUR || now()->getTimestamp() >= $window->start) {
                        return;
                    }
                }

                $forecast = NightForecast::forLocation($location, nights: 1)[0] ?? null;
                if (($forecast['verdict'] ?? null) !== 'clear_dark') {
                    return;
                }

                $sent++;
                $rows[] = [$user->id, $user->username, $location->name, $night, $forecast['clear_dark']['from'].'–'.$forecast['clear_dark']['until'], $forecast['cloud'].'%'];
                if ($apply) {
                    $this->send($user, $location->name, $forecast);
                    $user->forceFill(['clear_night_alert_night' => $night])->save();
                }
            });

        if ($rows !== [] && $this->output->isVerbose()) {
            $this->table(['Id', 'Username', 'Location', 'Night', 'Clear and dark', 'Clouds'], $rows);
        }

        $this->info(($apply ? 'Messages sent: ' : 'Messages to send: ').$sent);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $forecast  the night as NightForecast::night() gives it
     */
    private function send(User $user, string $locationName, array $forecast): void
    {
        try {
            $locale   = $user->language ?: config('app.locale');
            $location = html_entity_decode($locationName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $date     = Carbon::parse($forecast['night'])->locale($locale)->isoFormat('dddd LL');

            $subject = __('Clear night forecast at :location', ['location' => $location], $locale);
            $listUrl = route('observing-list.night', ['night' => $forecast['night']]);

            $body = '<p>'.e(__('The night of :date is forecast to be clear and dark at :location.', ['date' => $date, 'location' => $location], $locale)).'</p><ul>';
            $body .= '<li>'.e(__('Clear and dark :from–:until', $forecast['clear_dark'], $locale)).'</li>';
            $body .= '<li>'.e(__('Clouds :percent%', ['percent' => $forecast['cloud']], $locale)).'</li>';
            $body .= '<li>'.e($forecast['moon'] ? __('Moon :fraction%, up :from–:until', $forecast['moon'], $locale) : __('No Moon', [], $locale)).'</li>';
            if ($forecast['dew']) {
                $body .= '<li>'.e(__('Risk of dew', [], $locale)).'</li>';
            }
            $body .= '</ul><p><a href="'.e($listUrl).'">'.e(__('Make a list for this night', [], $locale)).'</a></p>';
            $body .= '<p>'.e(__('Weather data by :link', ['link' => 'Open-Meteo.com'], $locale)).'. '.e(__('You can turn this message off in your profile settings.', [], $locale)).'</p>';
            $body = Message::sanitizeHtml($body);

            $message = Message::create([
                'sender'   => 'deepskylog',
                'receiver' => $user->username,
                'subject'  => Message::sanitizeHtml($subject),
                'message'  => $body,
                'date'     => now(),
            ]);

            if ($user->sendMail) {
                Mail::to($user->email)
                    ->locale($locale)
                    ->send(new MessageReceived('DeepskyLog', $subject, $body, $user->name, route('messages.show', $message->id)));
            }

            // Until the night ends: a notification about it is no use later
            PushNotifications::send(
                $user,
                $subject,
                __('Clear and dark :from–:until', $forecast['clear_dark'], $locale).' · '.__('Clouds :percent%', ['percent' => $forecast['cloud']], $locale),
                $listUrl,
                'clear-night',
                ttl: 18 * 3600,
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send the clear night alert', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
