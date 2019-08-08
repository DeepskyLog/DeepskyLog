<?php

namespace App\Policies;

use App\User;
use App\Instrument;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstrumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the instrument.
     *
     * @param \App\User       $user       The user
     * @param \App\Instrument $instrument The instrument
     *
     * @return mixed
     */
    public function update(User $user, Instrument $instrument)
    {
        return $user->id === $instrument->observer_id;
    }
}
