<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NextForYou;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Calculates and caches the personal suggestions of an observer
 * (NextForYou), for the "Next for you" page.
 */
class ComputeNextForYou implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    public function __construct(public int $userId)
    {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        $next = $user ? NextForYou::for($user) : null;
        try {
            if ($next && $next->unavailable() === null) {
                $next->compute();
            }
        } finally {
            // the page queues it again on its next view
            $next?->forgetPending();
        }
    }
}
