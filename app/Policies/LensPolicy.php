<?php

namespace App\Policies;

use App\Lens;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LensPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the lens.
     *
     * @param  \App\User $user
     * @param  \App\Lens $lens
     * @return mixed
     */
    public function update(User $user, Lens $lens)
    {
        return $user->id == $lens->user_id;
    }
}
