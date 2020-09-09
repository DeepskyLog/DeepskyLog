<?php

namespace App\Policies;

use App\Models\Lens;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LensPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the lens.
     *
     * @param  \App\Models\User $user
     * @param  \App\Models\Lens $lens
     * @return mixed
     */
    public function update(User $user, Lens $lens)
    {
        return $user->id == $lens->user_id;
    }
}
