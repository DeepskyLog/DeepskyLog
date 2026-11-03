<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\DetectionThreshold;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Fits and stores the observer's own detection threshold
 * (DetectionThreshold), for the profile page.
 */
class FitDetectionThreshold implements ShouldQueue
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
        if (! $user) {
            return;
        }
        try {
            DetectionThreshold::compute($user);
        } finally {
            // the page queues it again on its next view
            DetectionThreshold::forgetPending($user);
        }
    }
}
