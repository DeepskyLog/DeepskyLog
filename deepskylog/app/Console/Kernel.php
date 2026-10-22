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
