<?php

namespace App\Models;

use App\Services\ActiveObservingListService;
use App\Traits\ClearsResponseCache;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ObservingList extends Model
{
    use ClearsResponseCache;
    use HasFactory, Sluggable;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'description',
        'public',
        'planned_timezone',
        'planned_night',
        'planned_location_id',
    ];

    protected $casts = [
        'public' => 'boolean',
        'planned_night' => 'date',
        'comments_count' => 'integer',
        'likes_count' => 'integer',
    ];

    protected static function booted(): void
    {
        // A private list is only for its owner: nobody else stays subscribed
        // to it or keeps it as active list.
        static::updated(function (ObservingList $list) {
            if ($list->wasChanged('public') && !$list->public) {
                $list->subscriptions()->delete();
                app(ActiveObservingListService::class)->clearForOthers($list);
            }
        });

        static::deleting(fn (ObservingList $list) => app(ActiveObservingListService::class)->clearForOthers($list, true));
    }

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
     * The order of the most popular lists: on their likes and subscribers
     * together, then (a subscription says more than a like) on the
     * subscribers, then on the comments, the newest list first. Adds
     * subscriptions_count.
     */
    public function scopeOrderByPopularity($query)
    {
        $subscribers = '(SELECT COUNT(*) FROM observing_list_subscriptions'
            .' WHERE observing_list_subscriptions.observing_list_id = observing_lists.id)';

        return $query->withCount('subscriptions')
            ->orderByRaw("(observing_lists.likes_count + {$subscribers}) desc")
            ->orderByRaw("{$subscribers} desc")
            ->orderBy('observing_lists.comments_count', 'desc')
            ->orderBy('observing_lists.created_at', 'desc')
            ->orderBy('observing_lists.id', 'desc');
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
            // Take back the point the like gave, or liking and unliking would
            // farm points for the owner.
            if ($user->id !== $this->owner_user_id && $this->owner?->experience()->exists()) {
                $this->owner->deductPoints(1);
            }
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

    /**
     * The lists of an observer have unique names: add " (2)", " (3)", ... to
     * a name that is taken.
     */
    public static function uniqueName(User $owner, string $name): string
    {
        $candidate = $name;
        for ($suffix = 2; self::where('owner_user_id', $owner->id)->where('name', $candidate)->exists(); $suffix++) {
            $candidate = $name . ' (' . $suffix . ')';
        }

        return $candidate;
    }

    /**
     * Create a list of the observer with these objects (objects.name), in
     * this order. The name is made unique. $details holds per object name a
     * note (item_description) and, for a list made for one night, the
     * moments (timestamps) to observe it and between which it stands high
     * enough. $plan gives that night (Y-m-d, its evening), the time zone the
     * times are shown in and the location.
     *
     * @param  iterable<string>  $objectNames
     * @param  array<string, array{note?: ?string, at?: ?int, from?: ?int, until?: ?int}>  $details
     * @param  array{night?: ?string, timezone?: ?string, location_id?: ?int}  $plan
     */
    public static function createWithObjects(User $owner, string $name, iterable $objectNames, ?string $description = null, bool $public = false, array $details = [], array $plan = []): self
    {
        $names = collect($objectNames)->map(fn ($name) => (string) $name)->filter(fn (string $name) => $name !== '')->unique()->values();
        $moment = fn (?int $timestamp) => $timestamp === null ? null : Carbon::createFromTimestamp($timestamp, 'UTC')->format('Y-m-d H:i:s');

        return DB::transaction(function () use ($owner, $name, $names, $description, $public, $details, $plan, $moment) {
            $list = self::create([
                'owner_user_id' => $owner->id,
                'name' => self::uniqueName($owner, $name),
                'description' => $description,
                'public' => $public,
                'planned_timezone' => $plan['timezone'] ?? null,
                'planned_night' => $plan['night'] ?? null,
                'planned_location_id' => $plan['location_id'] ?? null,
            ]);

            $now = now();
            foreach ($names->chunk(500) as $chunk) {
                DB::table('observing_list_items')->insert($chunk->map(fn (string $objectName, int $index) => [
                    'observing_list_id' => $list->id,
                    'object_name' => $objectName,
                    'item_description' => $details[$objectName]['note'] ?? null,
                    'planned_at' => $moment($details[$objectName]['at'] ?? null),
                    'planned_from' => $moment($details[$objectName]['from'] ?? null),
                    'planned_until' => $moment($details[$objectName]['until'] ?? null),
                    'sort_order' => $index + 1,
                    'source_mode' => 'manual',
                    'added_by_user_id' => $owner->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all());
            }

            return $list;
        });
    }

    /**
     * Whether the list was made for one night: some of its objects have a
     * time to observe them.
     */
    public function hasPlannedTimes(): bool
    {
        return $this->items()->whereNotNull('planned_at')->exists();
    }

    /**
     * Guests only see public lists, so changes on a private list do not
     * clear the response cache.
     */
    protected function affectsCachedPages(): bool
    {
        return (bool) $this->public;
    }
}
