<?php

namespace App\Policies;

use App\Location;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the location.
     *
     * @param \App\User     $user     The user
     * @param \App\Location $location The location
     *
     * @return mixed
     */
    public function update(User $user, Location $location)
    {
        return $user->id == $location->user_id;
    }
}
