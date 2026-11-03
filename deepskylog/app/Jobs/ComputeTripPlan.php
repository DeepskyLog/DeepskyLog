<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TripPlanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Calculates and caches the objects that are new for an observer at the
 * site of a trip (TripPlanner), for the trip planner page.
 */
class ComputeTripPlan implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    public function __construct(
        public int $userId,
        public array $trip,
        public int $instrumentId,
        public int $homeId
    ) {
    }

    public function handle(): void
    {
        $user    = User::find($this->userId);
        $planner = $user ? TripPlanner::for($user, $this->trip, $this->instrumentId, $this->homeId) : null;
        try {
            if ($planner && $planner->unavailable() === null) {
                $planner->compute();
            }
        } finally {
            // the page queues it again on its next view
            $planner?->forgetPending();
        }
    }
}
