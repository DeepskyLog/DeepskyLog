<?php

namespace App\Policies;

use App\Models\Eyepiece;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EyepiecePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the eyepiece.
     *
     * @param \App\Models\User     $user     The user
     * @param \App\Models\Eyepiece $eyepiece The eyepiece
     *
     * @return mixed
     */
    public function update(User $user, Eyepiece $eyepiece)
    {
        return $user->id == $eyepiece->user_id;
    }
}
