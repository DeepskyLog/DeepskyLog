<?php

namespace App\Models\Traits;

use App\Models\User;

/**
 * Equipment, locations and instrument sets belong to the user in `user_id`:
 * only that user, or an administrator, may change them.
 */
trait OwnedByUser
{
    public function canBeEditedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return (int) $this->user_id === (int) $user->id || $user->isAdministrator();
    }
}
