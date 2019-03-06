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
     * @param \App\User $user The user object
     * @param \App\Lens $lens The lens object
     *
     * @return mixed true if the user can view the lens.
     */
    public function view(User $user, Lens $lens)
    {
        // TODO: Write the policies
        return $lens->observer_id == $user->id;
    }

    /**
     * Determine whether the user can create lenses.
     *
     * @param \App\User $user The user object
     *
     * @return mixed true if the user can create the lens.
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the lenses.
     *
     * @param \App\User $user The user object
     * @param \App\Lens $lens The lens object
     *
     * @return mixed true if the user can update the lens.
     */
    public function update(User $user, Lens $lens)
    {
        //
    }

    /**
     * Determine whether the user can delete the lenses.
     *
     * @param \App\User $user The user object
     * @param \App\Lens $lens The lens object
     *
     * @return mixed true if the user can delete the lens.
     */
    public function delete(User $user, Lens $lens)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the lenses.
     *
     * @param \App\User $user The user object
     * @param \App\Lens $lens The lens object
     *
     * @return mixed true if the user can delete the lens.
     */
    public function forceDelete(User $user, Lens $lens)
    {
        //
    }
}
