<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        // Only prune stale tags when Redis is used as the cache store. The
        // framework command returns exit code 1 when run for non-Redis stores
        // which triggers scheduler failures. Guard scheduling to avoid that.
        if (config('cache.default') === 'redis') {
            $schedule->command('cache:prune-stale-tags')->hourly();
        }
        // Backfill session slugs (idempotent). Sessions saved through the model
        // get a slug from ObservationSession::sluggable(); this only catches
        // sessions written without one (imports, the old site).
        $schedule->command('sessions:backfill-slugs')->hourly();

        // The privacy policy: accounts that recorded nothing within 24 months
        // after registration are warned a month and a week before, and then
        // deleted. Daily, so the warnings go out on time.
        $schedule->command('users:prune-inactive --apply')->dailyAt('03:30')->withoutOverlapping();

        // The country of residence of new observers, from their first
        // locations, so the national rankings and filters include them.
        $schedule->command('users:fill-country --apply')->dailyAt('03:45')->withoutOverlapping();

        // The daily message with the new observations of the observers
        // someone follows, for those who asked for it on the profile page.
        $schedule->command('follows:send-digest --apply')->dailyAt('07:00')->withoutOverlapping();

        // The reminder of the starparties (#315) that start in the coming
        // days, to the observers who come.
        $schedule->command('star-parties:send-reminders --apply')->dailyAt('08:00')->withoutOverlapping();

        // The reminder of the sessions of the last days without any
        // observation of their observer, to finish logging the night.
        $schedule->command('sessions:send-observation-reminders --apply')->dailyAt('10:00')->withoutOverlapping();

        // The message with the review of the year that just ended. Daily in
        // the first week of January, so a failed run is made up the next day;
        // each observer gets it once (users.year_review_sent_year).
        $schedule->command('observers:send-year-review --apply')->dailyAt('10:30')
            ->when(fn () => now()->month === 1 && now()->day <= 7)->withoutOverlapping();

        // The message when a clear, dark night is forecast at an observer's
        // standard location: hourly, as noon comes at a different hour for
        // each location.
        $schedule->command('forecast:send-clear-night-alerts --apply')->hourlyAt(15)->withoutOverlapping();

        // The news items of the coming eclipses, conjunctions, ... (#960),
        // published a week before the event.
        $schedule->command('news:astronomical-events --apply')->dailyAt('02:30')->withoutOverlapping();

        // The descriptions of new observations translated into the languages
        // of the site for the guests (#310), and the older ones gradually,
        // newest first, a few at a time to stay under Google's rate limit.
        $schedule->command('observations:translate-descriptions --apply')->everyTenMinutes()->withoutOverlapping();

        $schedule->command('astronomy:updateCometPhotometry')->weeklyOn(2, '4:30');
        // Incremental search index reindex (one-run nightly; safe for production)
        $schedule->command('search:reindex --incremental')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        // Commands in app/Console/Commands will be autoloaded by $this->load().
        require base_path('routes/console.php');
    }
}
