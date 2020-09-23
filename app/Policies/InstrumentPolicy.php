<?php

namespace App\Policies;

use App\Models\Instrument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstrumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the instrument.
     *
     * @param \App\Models\User       $user       The user
     * @param \App\Models\Instrument $instrument The instrument
     *
     * @return mixed
     */
    public function update(User $user, Instrument $instrument)
    {
        return $user->id == $instrument->user_id;
    }
}
