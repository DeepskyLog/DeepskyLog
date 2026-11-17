<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An observing list an observer is working on ("Working on"): their own list
 * or a public list they subscribed to, at most MAX_PER_USER. Only for the
 * observer: it counts for nothing else (not the popularity of a list).
 */
class ObservingListPin extends Model
{
    public const MAX_PER_USER = 20;

    protected $fillable = [
        'user_id',
        'observing_list_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(ObservingList::class, 'observing_list_id');
    }

    /**
     * The ids of the lists the observer is working on, most recently marked first.
     *
     * @return array<int, int>
     */
    public static function idsFor(?int $userId): array
    {
        if (! $userId) {
            return [];
        }

        return self::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('observing_list_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Marks the list as worked on. False when the observer already works on
     * MAX_PER_USER lists; true when it is (or already was) marked.
     */
    public static function pin(User $user, ObservingList $list): bool
    {
        if (self::where('user_id', $user->id)->where('observing_list_id', $list->id)->exists()) {
            return true;
        }
        if (self::where('user_id', $user->id)->count() >= self::MAX_PER_USER) {
            return false;
        }
        self::create(['user_id' => $user->id, 'observing_list_id' => $list->id]);

        return true;
    }

    public static function unpin(User $user, ObservingList $list): void
    {
        self::where('user_id', $user->id)->where('observing_list_id', $list->id)->delete();
    }

    /**
     * Puts the options of the lists the observer is working on first, keeping
     * the order of the others (options keyed on the list id, or on a key that
     * $idOf turns into one).
     *
     * @param  array<int|string, mixed>  $options
     * @return array<int|string, mixed>
     */
    public static function first(array $options, ?int $userId, ?callable $idOf = null): array
    {
        $pinned = array_flip(self::idsFor($userId));
        if ($pinned === []) {
            return $options;
        }
        $idOf ??= fn ($key) => (int) $key;

        $first = [];
        $rest = [];
        foreach ($options as $key => $value) {
            if (isset($pinned[$idOf($key, $value)])) {
                $first[$key] = $value;
            } else {
                $rest[$key] = $value;
            }
        }

        return $first + $rest;
    }
}
