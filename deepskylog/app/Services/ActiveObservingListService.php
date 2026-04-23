<?php

namespace App\Services;

use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;

class ActiveObservingListService
{
    /**
     * Set the active observing list for a user.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function setActiveList(User $user, ObservingList $list): bool
    {
        // Check authorization: user must own or be subscribed to the list
        if ($user->id !== $list->owner_user_id && !$list->isSubscribedBy($user)) {
            throw new \Exception('User is not authorized to set this list as active');
        }

        // Avoid mass-assignment issues on User when persisting the active list id.
        $user->forceFill(['active_observing_list_id' => $list->id])->save();

        return true;
    }

    /**
     * Clear the active observing list for a user.
     */
    public function clearActiveList(User $user): bool
    {
        // Avoid mass-assignment issues on User when clearing the active list id.
        $user->forceFill(['active_observing_list_id' => null])->save();

        return true;
    }

    /**
     * Get the user's active observing list.
     */
    public function getActiveList(User $user): ?ObservingList
    {
        return $user->activeObservingList;
    }

    /**
     * Check if a list is the user's active list.
     */
    public function isActive(User $user, ObservingList $list): bool
    {
        return (int) $user->active_observing_list_id === (int) $list->id;
    }
}
