<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Job that enqueues per-object compute jobs for a user's instrument/location by
 * invoking the metrics:compute-cr artisan command with --queued.
 */
class EnqueueComputeCRForUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public ?int $instrumentId;
    public ?int $locationId;

    public function __construct(int $userId, ?int $instrumentId, ?int $locationId)
    {
        $this->userId = $userId;
        $this->instrumentId = $instrumentId;
        $this->locationId = $locationId;
    }

    public function handle(): void
    {
        try {
            $args = ['user_id' => $this->userId, '--queued' => true];
            if ($this->instrumentId) {
                $args['--instrument_id'] = $this->instrumentId;
            }
            if ($this->locationId) {
                $args['--location_id'] = $this->locationId;
            }

            Log::info('EnqueueComputeCRForUser: invoking metrics:compute-cr', $args);
            // This will dispatch per-object jobs (ComputeContrastReserveForObject)
            Artisan::call('metrics:compute-cr', $args);
        } catch (\Throwable $ex) {
            Log::error('EnqueueComputeCRForUser: failed to enqueue compute-cr', ['error' => $ex->getMessage(), 'user' => $this->userId]);
        }
    }
}
