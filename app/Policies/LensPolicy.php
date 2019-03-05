<?php

namespace App\Policies;

use App\User;
use App\Lenses;
use Illuminate\Auth\Access\HandlesAuthorization;

class LensPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lenses  $lenses
     * @return mixed
     */
    public function view(User $user, Lenses $lenses)
    {
        // TODO: Write the policies
        // TODO: Rename Lenses to Lens
        return $lenses->observer_id == $user->id;
    }

    /**
     * Determine whether the user can create lenses.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lenses  $lenses
     * @return mixed
     */
    public function update(User $user, Lenses $lenses)
    {
        //
    }

    /**
     * Determine whether the user can delete the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lenses  $lenses
     * @return mixed
     */
    public function delete(User $user, Lenses $lenses)
    {
        //
    }

    /**
     * Determine whether the user can restore the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lenses  $lenses
     * @return mixed
     */
    public function restore(User $user, Lenses $lenses)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lenses  $lenses
     * @return mixed
     */
    public function forceDelete(User $user, Lenses $lenses)
    {
        //
    }
}
