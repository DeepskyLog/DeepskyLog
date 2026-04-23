<?php

namespace App\Policies;

use App\Models\ObservingList;
use App\Models\ObservingListComment;
use App\Models\User;

class ObservingListPolicy
{
    /**
     * Determine if the user can view the observing list.
     */
    public function view(User $user, ObservingList $list): bool
    {
        // Owner can always view
        if ($user->id === $list->owner_user_id) {
            return true;
        }

        // Public lists can be viewed by anyone
        if ($list->public) {
            return true;
        }

        // Subscribers can view
        if ($list->isSubscribedBy($user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create an observing list.
     */
    public function create(User $user): bool
    {
        // Must be verified observer
        return $user->isObserver();
    }

    /**
     * Determine if the user can update the observing list.
     */
    public function update(User $user, ObservingList $list): bool
    {
        // Only owner can update
        return $user->id === $list->owner_user_id;
    }

    /**
     * Determine if the user can delete the observing list.
     */
    public function delete(User $user, ObservingList $list): bool
    {
        // Only owner or admin can delete
        return $user->id === $list->owner_user_id || $user->isAdministrator();
    }

    /**
     * Determine if the user can add items to the observing list.
     */
    public function addItem(User $user, ObservingList $list): bool
    {
        // Only owner can add items
        return $user->id === $list->owner_user_id;
    }

    /**
     * Determine if the user can remove items from the observing list.
     */
    public function removeItem(User $user, ObservingList $list): bool
    {
        // Only owner can remove items
        return $user->id === $list->owner_user_id;
    }

    /**
     * Determine if the user can subscribe to the observing list.
     */
    public function subscribe(User $user, ObservingList $list): bool
    {
        // Cannot subscribe to own lists
        if ($user->id === $list->owner_user_id) {
            return false;
        }

        // Only public lists can be subscribed to
        if (!$list->public) {
            return false;
        }

        // Cannot subscribe twice
        if ($list->isSubscribedBy($user)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the user can unsubscribe from the observing list.
     */
    public function unsubscribe(User $user, ObservingList $list): bool
    {
        // Must be subscribed
        return $list->isSubscribedBy($user);
    }

    /**
     * Determine if the user can like the observing list.
     */
    public function like(User $user, ObservingList $list): bool
    {
        // Cannot like own lists
        if ($user->id === $list->owner_user_id) {
            return false;
        }

        // Only public lists can be liked
        return $list->public;
    }

    /**
     * Determine if the user can comment on the observing list.
     */
    public function comment(User $user, ObservingList $list): bool
    {
        // Cannot comment on private lists you don't own
        if (!$list->public && $user->id !== $list->owner_user_id) {
            return false;
        }

        // If public or owner, can comment
        return true;
    }

    /**
     * Determine if the user can delete a comment on the observing list.
     */
    public function deleteComment(User $user, ObservingListComment $comment): bool
    {
        return $comment->canBeDeletedBy($user);
    }

    /**
     * Determine if the user can set this list as their active list.
     */
    public function setAsActive(User $user, ObservingList $list): bool
    {
        // Can only set as active if owner or subscribed
        return $user->id === $list->owner_user_id || $list->isSubscribedBy($user);
    }

    /**
     * Determine if the user can make the list public.
     */
    public function makePublic(User $user, ObservingList $list): bool
    {
        // Only owner can change publicity
        return $user->id === $list->owner_user_id;
    }
}
