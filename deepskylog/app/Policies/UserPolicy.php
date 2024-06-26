<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determines if a user can delete other users.
     *
     * This method checks if the given user is an administrator. Only administrators have the permission to delete other users.
     * The method returns true if the user is an administrator, and false otherwise.
     *
     * @param  User  $user  The user whose permissions are being checked.
     * @return bool True if the user is an administrator, false otherwise.
     */
    public function delete(User $user): bool
    {
        return $user->isAdministrator();
    }

    /**
     * Determines if a user can add sketches.
     *
     * This method checks if the given user is an administrator or a database expert.
     * Only administrators and database experts have the permission to add sketches.
     * The method returns true if the user is an administrator or a database expert, and false otherwise.
     *
     * @param  User  $user  The user whose permissions are being checked.
     * @return bool True if the user is an administrator or a database expert, false otherwise.
     */
    public function add_sketch(User $user): bool
    {
        return $user->isAdministrator() || $user->isDatabaseExpert();
    }
}
