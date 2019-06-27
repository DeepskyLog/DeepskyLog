<?php

namespace App\Policies;

use App\User;
use App\Filter;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the filter.
     *
     * @param \App\User   $user   The user
     * @param \App\Filter $filter The filter
     *
     * @return mixed
     */
    public function update(User $user, Filter $filter)
    {
        return $user->id === $filter->observer_id;
    }
}
