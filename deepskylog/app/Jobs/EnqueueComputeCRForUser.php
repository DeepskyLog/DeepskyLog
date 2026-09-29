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
 * invoking the metrics:compute-cr artisan command with --queued; lens optional.
 */
class EnqueueComputeCRForUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public ?int $instrumentId;
    public ?int $locationId;
    public ?int $lensId;

    public function __construct(int $userId, ?int $instrumentId, ?int $locationId, ?int $lensId = null)
    {
        $this->userId = $userId;
        $this->instrumentId = $instrumentId;
        $this->locationId = $locationId;
        $this->lensId = $lensId;
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
            if ($this->lensId) {
                $args['--lens_id'] = $this->lensId;
            }

            Log::info('EnqueueComputeCRForUser: invoking metrics:compute-cr', $args);
            // This will dispatch per-object jobs (ComputeContrastReserveForObject)
            Artisan::call('metrics:compute-cr', $args);
        } catch (\Throwable $ex) {
            Log::error('EnqueueComputeCRForUser: failed to enqueue compute-cr', ['error' => $ex->getMessage(), 'user' => $this->userId]);
        }
    }
}
