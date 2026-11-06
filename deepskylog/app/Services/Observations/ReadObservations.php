<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Which observations an observer has not read yet: those with an id above
 * their mark, the user_preferences row PREFERENCE. The old site keeps its
 * "Latest observations" mark (lastReadObservationId of legacy_observers) in
 * the same row, so what is read on one site is read on the other.
 *
 * The mark only moves forward, and only to an id the page showed (never "the
 * newest id now"), so observations added while a page was open stay unread.
 * The observer's own observations never count as unread.
 */
class ReadObservations
{
    public const PREFERENCE = 'legacy_last_read_observation';

    /** The target types of the global observation listing. */
    public const TARGET_TYPES = ['deepsky', 'comet', 'planet', 'solarsystem', 'lunar'];

    /**
     * The id of the last observation the observer has read. An observer
     * without a mark starts with everything read.
     */
    public static function mark(User $user): int
    {
        $value = DB::table('user_preferences')->where('user_id', $user->id)->where('key', self::PREFERENCE)->value('value');
        if ($value === null || $value === '' || !ctype_digit((string) $value)) {
            $value = self::store($user, self::newestId());
        }

        return (int) $value;
    }

    /**
     * Mark the observations up to and including $id as read. Never moves the
     * mark back, nor past the newest observation.
     */
    public static function markReadUpTo(User $user, int $id): int
    {
        $id = min($id, self::newestId());
        $mark = self::mark($user);

        return $id > $mark ? self::store($user, $id) : $mark;
    }

    /**
     * Restrict a query on observations to those the observer has not read.
     */
    public static function scopeUnread(Builder $query, User $user): Builder
    {
        return $query->where('observations.id', '>', self::mark($user))
            ->where('observations.observer_username', '!=', $user->username);
    }

    /**
     * The number of unread observations of the global listing.
     */
    public static function unreadCount(User $user): int
    {
        return self::scopeUnread(Observation::query()->whereIn('target_type', self::TARGET_TYPES), $user)->count();
    }

    /**
     * Whether the observation is one the observer with this mark has not read.
     */
    public static function isUnread(User $user, int $mark, Observation $observation): bool
    {
        return $observation->id > $mark && $observation->observer_username !== $user->username;
    }

    public static function newestId(): int
    {
        return (int) Observation::query()->max('id');
    }

    private static function store(User $user, int $id): int
    {
        DB::table('user_preferences')->upsert(
            [['user_id' => $user->id, 'key' => self::PREFERENCE, 'value' => (string) $id, 'created_at' => now(), 'updated_at' => now()]],
            ['user_id', 'key'],
            ['value', 'updated_at']
        );

        return $id;
    }
}
