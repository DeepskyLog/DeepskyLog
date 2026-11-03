<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RevisitObjects;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Calculates and caches the objects an observer has seen that their
 * telescope shows much better (RevisitObjects), for the "Revisit with your
 * telescope" page.
 */
class ComputeRevisitObjects implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    public function __construct(
        public int $userId,
        public int $instrumentId,
        public int $locationId
    ) {
    }

    public function handle(): void
    {
        $user    = User::find($this->userId);
        $revisit = $user ? RevisitObjects::for($user, $this->instrumentId, $this->locationId) : null;
        try {
            if ($revisit && $revisit->unavailable() === null) {
                $revisit->compute();
            }
        } finally {
            // the page queues it again on its next view
            $revisit?->forgetPending();
        }
    }
}
