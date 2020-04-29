<?php

namespace App\Policies;

use App\Eyepiece;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EyepiecePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the eyepiece.
     *
     * @param \App\User     $user     The user
     * @param \App\Eyepiece $eyepiece The eyepiece
     *
     * @return mixed
     */
    public function update(User $user, Eyepiece $eyepiece)
    {
        return $user->id == $eyepiece->user_id;
    }
}
