<?php

namespace App\Console\Commands;

use App\Models\DeepskyObject;
use App\Jobs\ComputeContrastReserveForObject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Compute contrast reserve for a user's instrument/location and store it in the
 * user_object_metrics table.
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
        $chunk = (int) $this->option('chunk');
        $lensId = $this->option('lens_id') ? (int) $this->option('lens_id') : null;

        $this->info("Computing CR for user {$userId} instrument={$instrumentId} location={$locationId}");

        // We'll iterate objects in chunks to avoid memory pressure
        $queued = (bool) $this->option('queued');

        // Ensure we order by a real column present on the legacy `objects` table
        // (this avoids Laravel trying to ORDER BY `objects`.`id` when no id column exists)
        DeepskyObject::query()->orderBy('name')->chunk($chunk, function ($objects) use ($userId, $instrumentId, $locationId, $queued, $lensId) {
            foreach ($objects as $obj) {
                try {
                    if ($queued) {
                        // Dispatch a job per object to compute CR asynchronously
                        ComputeContrastReserveForObject::dispatch($userId, $instrumentId, $locationId, $obj->name, $lensId);
                        continue;
                    }

                    // Synchronous path: the same calculation, stored under the same
                    // key (including the lens), as the queued job
                    (new ComputeContrastReserveForObject($userId, $instrumentId, $locationId, $obj->name, $lensId))->handle();
                } catch (\Throwable $ex) {
                    // swallow to continue chunk processing, but log
                    Log::debug('ComputeContrastReserve: object compute failed', ['object' => $obj->name ?? null, 'error' => $ex->getMessage()]);
                    continue;
                }
            }
        });

        $this->info('Done computing metrics');
        return Command::SUCCESS;
    }
}
