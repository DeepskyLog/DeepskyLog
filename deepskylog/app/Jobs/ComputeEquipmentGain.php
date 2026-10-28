<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\EquipmentGain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Calculates and caches the objects an observer's telescope or eyepiece
 * lets them see that they could not see before (EquipmentGain), for the
 * "What's new for me" page.
 */
class ComputeEquipmentGain implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300;

    public $tries = 1;

    public function __construct(
        public int $userId,
        public string $kind,
        public int $equipmentId,
        public int $locationId,
        public ?array $compareIds = null
    ) {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        $gain = $user ? EquipmentGain::for($user, $this->kind, $this->equipmentId, $this->locationId, $this->compareIds) : null;
        try {
            if ($gain && $gain->unavailable() === null) {
                $gain->compute();
            }
        } finally {
            // the page queues it again on its next view
            $gain?->forgetPending();
        }
    }
}
