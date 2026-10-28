<?php

namespace App\Console\Commands;

use App\Jobs\ComputeContrastReserveForObjectsBatch;
use App\Services\ObjectMetricsQueue;
use Illuminate\Console\Command;

/**
 * Compute contrast reserve for a user's instrument/location and store it in the
 * user_object_metrics table, replacing the stored values.
 */
class ComputeContrastReserve extends Command
{
    protected $signature = 'metrics:compute-cr {user_id} {--instrument_id=} {--location_id=} {--chunk=500} {--queued} {--lens_id=}';

    protected $description = 'Compute Contrast Reserve for a user/instrument/location and cache results in user_object_metrics';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $instrumentId = $this->option('instrument_id') ? (int) $this->option('instrument_id') : null;
        $locationId = $this->option('location_id') ? (int) $this->option('location_id') : null;
        $chunk = max(1, (int) $this->option('chunk'));
        $lensId = $this->option('lens_id') ? (int) $this->option('lens_id') : null;

        if (! $instrumentId || ! $locationId) {
            $this->error('An instrument and a location are needed.');

            return Command::FAILURE;
        }

        $this->info("Computing CR for user {$userId} instrument={$instrumentId} location={$locationId}");

        if ($this->option('queued')) {
            // Jobs of ObjectMetricsQueue::NAMES_PER_JOB objects, each loading the equipment once
            $jobs = ObjectMetricsQueue::queueRecompute($userId, $instrumentId, $locationId, $lensId);
            $this->info("Queued {$jobs} jobs");

            return Command::SUCCESS;
        }

        // Synchronous path: the same calculation, stored under the same key
        // (including the lens), as the queued jobs
        foreach (ObjectMetricsQueue::allObjectNameChunks() as $names) {
            foreach (array_chunk($names, $chunk) as $part) {
                (new ComputeContrastReserveForObjectsBatch($userId, $instrumentId, $locationId, $part, $lensId, null, true))->handle();
            }
        }

        $this->info('Done computing metrics');

        return Command::SUCCESS;
    }
}
