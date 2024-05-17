<?php

namespace App\Policies;

use App\Models\test;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class testPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {

    }

    public function view(User $user, test $test): bool
    {
    }

    public function create(User $user): bool
    {
    }

    public function update(User $user, test $test): bool
    {
    }

    public function delete(User $user, test $test): bool
    {
    }

    public function restore(User $user, test $test): bool
    {
    }

    public function forceDelete(User $user, test $test): bool
    {
    }
}
