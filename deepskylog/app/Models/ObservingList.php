<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservingList extends Model
{
    use HasFactory, Sluggable;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'public',
    ];

    protected $casts = [
        'public' => 'boolean',
        'comments_count' => 'integer',
        'likes_count' => 'integer',
    ];

    /**
     * Return the sluggable configuration for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name_decoded',
                'unique' => true,
            ],
        ];
    }

    /**
     * Decoded name used as slug source (handles legacy HTML entities).
     */
    public function getNameDecodedAttribute(): string
    {
        return html_entity_decode($this->attributes['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Use slug for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the owner of this observing list.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get the items in this observing list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ObservingListItem::class);
    }

    /**
     * Get the subscriptions for this observing list.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(ObservingListSubscription::class);
    }

    /**
     * Get the comments on this observing list.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ObservingListComment::class);
    }

    /**
     * Get the likes on this observing list.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(ObservingListLike::class);
    }

    /**
     * Get users who have subscribed to this list.
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'observing_list_subscriptions', 'observing_list_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Check if a user is subscribed to this list.
     */
    public function isSubscribedBy(User $user): bool
    {
        return $this->subscribers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user has liked this list.
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Toggle like status for a user.
     */
    public function toggleLike(User $user): bool
    {
        $like = $this->likes()->where('user_id', $user->id)->first();

        if ($like) {
            $like->delete();
            $this->decrement('likes_count');
            return false; // Unlike
        } else {
            $this->likes()->create(['user_id' => $user->id]);
            $this->increment('likes_count');
            // Award points to list owner if user is different
            if ($user->id !== $this->owner_user_id) {
                $this->owner->addPoints(1);
            }
            return true; // Like
        }
    }

    /**
     * Add a comment to this list.
     */
    public function addComment(User $user, string $body): ObservingListComment
    {
        $comment = $this->comments()->create([
            'user_id' => $user->id,
            'body' => $body,
        ]);

        $this->increment('comments_count');

        return $comment;
    }

    /**
     * Remove a comment from this list.
     */
    public function removeComment(ObservingListComment $comment): void
    {
        $comment->delete();
        $this->decrement('comments_count');
    }
}
