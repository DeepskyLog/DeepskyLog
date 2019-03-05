<?php

namespace App\Policies;

use App\User;
use App\Lens;
use Illuminate\Auth\Access\HandlesAuthorization;

class LensPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the lens.
     *
     * @param  \App\User  $user
     * @param  \App\Lens  $lens
     * @return mixed
     */
    public function view(User $user, Lens $lens)
    {
        // TODO: Write the policies
        return $lens->observer_id == $user->id;
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
     * @param  \App\Lens  $lens
     * @return mixed
     */
    public function update(User $user, Lens $lens)
    {
        //
    }

    /**
     * Determine whether the user can delete the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lens  $lens
     * @return mixed
     */
    public function delete(User $user, Lens $lens)
    {
        //
    }

    /**
     * Determine whether the user can restore the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lens  $lens
     * @return mixed
     */
    public function restore(User $user, Lens $lens)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the lenses.
     *
     * @param  \App\User  $user
     * @param  \App\Lens  $lens
     * @return mixed
     */
    public function forceDelete(User $user, Lens $lens)
    {
        //
    }
}
