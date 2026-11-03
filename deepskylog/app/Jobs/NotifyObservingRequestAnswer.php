<?php

namespace App\Jobs;

use App\Models\Observation;
use App\Models\ObservingRequest;
use App\Services\ObservingRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * The message that an observation answers an observing request, queued
 * DELAY_SECONDS after the observation was created: the form saves whether
 * the object was seen after the observation itself. Nothing is sent when the
 * observation or the request was deleted meanwhile.
 */
class NotifyObservingRequestAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const DELAY_SECONDS = 60;

    public function __construct(
        public int $requestId,
        public int $observationId,
    ) {
    }

    public function handle(): void
    {
        $request = ObservingRequest::with('user')->find($this->requestId);
        $observation = Observation::find($this->observationId);
        if ($request && $observation) {
            ObservingRequests::notify($request, $observation);
        }
    }
}
