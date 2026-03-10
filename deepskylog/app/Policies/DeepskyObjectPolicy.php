<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeepskyObject;

class DeepskyObjectPolicy
{
    /**
     * Determine if the user can update the object.
     * Only Administrators and Database Experts can edit objects.
     */
    public function update(User $user, DeepskyObject $object): bool
    {
        return $user->isAdministrator() || $user->isDatabaseExpert();
    }

    /**
     * Determine if the user can view the edit form.
     * Only Administrators and Database Experts can access the edit form.
     */
    public function edit(User $user, DeepskyObject $object): bool
    {
        return $user->isAdministrator() || $user->isDatabaseExpert();
    }
}
