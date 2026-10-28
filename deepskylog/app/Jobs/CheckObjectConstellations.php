<?php

namespace App\Jobs;

use App\Services\AdminObjectCheckService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Compares the stored constellation of every object with the one of its
 * coordinates, and corrects it when $repair is set. The result goes to the
 * cache, where /admin/check-objects shows it (see AdminObjectCheckService).
 *
 * The library takes about 5 ms per object, minutes for all of them, so the
 * job goes to the long-running connection of config/queue.php: on the
 * default connection a second worker would pick it up again after 90 seconds.
 */
class CheckObjectConstellations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public bool $repair = false)
    {
        if (is_string($connection = config('queue.long_running'))) {
            $this->onConnection($connection);
        }
    }

    public function handle(AdminObjectCheckService $service): void
    {
        $service->runConstellationCheck($this->repair);
    }

    /**
     * Called when the job died outside handle(): it ran out of time, or the
     * worker was killed. Without this the page would say "running" for ever.
     */
    public function failed(?Throwable $exception): void
    {
        app(AdminObjectCheckService::class)->markConstellationCheckFailed();
    }
}
