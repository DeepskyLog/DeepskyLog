<?php

namespace App\Jobs;

use App\Services\ObjectMetricsQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job that recomputes the metrics of all objects for a user's instrument/location
 * (lens optional), by queueing ComputeContrastReserveForObjectsBatch jobs of
 * ObjectMetricsQueue::NAMES_PER_JOB objects. Unique per user and equipment, so
 * changing the standard equipment back and forth queues it once.
 */
class EnqueueComputeCRForUser implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * How long the uniqueness lock is kept when the job never runs.
     *
     * @var int
     */
    public $uniqueFor = 3600;

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

    public function uniqueId(): string
    {
        return implode(':', [$this->userId, $this->instrumentId ?? '-', $this->locationId ?? '-', $this->lensId ?? '-']);
    }

    public function handle(): void
    {
        // Without an instrument and a location there is nothing to compute.
        if (! $this->instrumentId || ! $this->locationId) {
            return;
        }

        try {
            $jobs = ObjectMetricsQueue::queueRecompute($this->userId, $this->instrumentId, $this->locationId, $this->lensId);
            Log::info('EnqueueComputeCRForUser: queued metrics recompute', ['user' => $this->userId, 'jobs' => $jobs]);
        } catch (\Throwable $ex) {
            Log::error('EnqueueComputeCRForUser: failed to enqueue compute-cr', ['error' => $ex->getMessage(), 'user' => $this->userId]);
        }
    }
}
