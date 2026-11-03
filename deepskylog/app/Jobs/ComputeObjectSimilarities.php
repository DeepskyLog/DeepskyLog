<?php

namespace App\Jobs;

use App\Services\AlsoSeenObjects;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Recalculates "Observers who saw this also saw…" (AlsoSeenObjects::rebuild()),
 * weekly. It takes about half a minute, longer than the default worker's
 * timeout, so the job goes to the long-running connection of
 * config/queue.php.
 */
class ComputeObjectSimilarities implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 1800;

    public function __construct()
    {
        if (is_string($connection = config('queue.long_running'))) {
            $this->onConnection($connection);
        }
    }

    public function handle(): void
    {
        AlsoSeenObjects::rebuild();
    }
}
