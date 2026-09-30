<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObservingListComment extends Model
{
    use ClearsResponseCache;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'observing_list_id',
        'user_id',
        'body',
    ];

    /**
     * Get the observing list this comment belongs to.
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class, 'observing_list_id');
    }

    /**
     * Get the user who wrote this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user can delete this comment.
     * Allowed for: comment author, list owner, or admin.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $user->id === $this->user_id
            || $user->id === $this->list->owner_user_id
            || $user->isAdministrator();
    }

    /**
     * Guests only see public lists, so changes on a private list do not
     * clear the response cache.
     */
    protected function affectsCachedPages(): bool
    {
        return ObservingList::whereKey($this->observing_list_id)->where('public', true)->exists();
    }
}
