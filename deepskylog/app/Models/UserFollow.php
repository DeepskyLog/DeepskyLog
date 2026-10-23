<?php

namespace App\Models;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An observer (follower_id) following another (followed_id): the followed
 * observer's new observations show up in the follower's "Following" view of
 * the observations, their personal RSS feed and daily digest. The observer
 * page shows the number of followers, also to guests, hence the cache trait.
 */
class UserFollow extends Model
{
    use ClearsResponseCache;

    protected $fillable = ['follower_id', 'followed_id'];

    public function follower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function followed(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_id');
    }
}
