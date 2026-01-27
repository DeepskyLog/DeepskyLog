<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to compute and persist contrast reserve for multiple objects in one batch.
 */
class ComputeContrastReserveForObjectsBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public ?int $instrumentId;
    public ?int $locationId;
    public array $objectNames;
    public ?int $lensId;

    public function __construct(int $userId, ?int $instrumentId, ?int $locationId, array $objectNames, ?int $lensId = null)
    {
        $this->userId = $userId;
        $this->instrumentId = $instrumentId;
        $this->locationId = $locationId;
        $this->objectNames = $objectNames;
        $this->lensId = $lensId;
    }

    public function handle(): void
    {
        $start = microtime(true);
        $total = 0;
        $succeeded = 0;
        $failed = 0;
        try {
            Log::debug('ComputeContrastReserveForObjectsBatch: start', ['user_id' => $this->userId, 'instrument_id' => $this->instrumentId, 'location_id' => $this->locationId, 'count' => count($this->objectNames), 'lens_id' => $this->lensId]);
            foreach ($this->objectNames as $oname) {
                $total++;
                try {
                    // Reuse existing single-object job logic by instantiating
                    // the single-object job and invoking its handle() method
                    // synchronously within this batch job. This keeps behavior
                    // identical while avoiding dispatching many separate jobs.
                    $job = new ComputeContrastReserveForObject($this->userId, $this->instrumentId, $this->locationId, $oname, $this->lensId);
                    $job->handle();
                    $succeeded++;
                } catch (\Throwable $ex) {
                    $failed++;
                    Log::debug('ComputeContrastReserveForObjectsBatch: per-object compute failed', ['object' => $oname, 'error' => (string)$ex]);
                    // continue with other objects
                }
            }
        } catch (\Throwable $ex) {
            Log::debug('ComputeContrastReserveForObjectsBatch: batch job failed', ['error' => (string)$ex]);
        } finally {
            $elapsed = round((microtime(true) - $start) * 1000, 2);
            Log::debug('ComputeContrastReserveForObjectsBatch: end', ['user_id' => $this->userId, 'instrument_id' => $this->instrumentId, 'location_id' => $this->locationId, 'count' => $total, 'succeeded' => $succeeded, 'failed' => $failed, 'elapsed_ms' => $elapsed]);
        }
    }
}
