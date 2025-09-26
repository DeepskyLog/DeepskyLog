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
        // Backfill session slugs regularly (idempotent)
        $schedule->command('sessions:backfill-slugs')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
