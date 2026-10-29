<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * An observer following an object (a deep-sky object, comet, planet, moon,
 * lunar feature or asteroid), keyed like the observations (see the target
 * scheme in App\Models\Observation): the object's new observations by
 * others go into the observer's "Following" view of the observations, their
 * personal RSS feed and daily digest, like those of the observers they
 * follow. A deep-sky object is keyed on objects.name, so renaming or merging
 * it calls moveDeepskyObject().
 */
class ObjectFollow extends Model
{
    /** An observer follows at most this many objects. */
    public const MAX_PER_USER = 200;

    /** The tables of the objects that can be followed, with their observations.target_type. */
    public const TABLES = [
        'objects' => Observation::TYPE_DEEPSKY,
        'cometobjects' => Observation::TYPE_COMET,
        'planets' => 'planet',
        'moons' => 'lunar',
        'lunar_features' => 'lunar',
        'asteroids' => 'solarsystem',
    ];

    protected $fillable = ['user_id', 'target_type', 'target_table', 'target_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The object of a table and key, as the observations store it: null when
     * it is not there.
     *
     * @return array{type: string, table: string, key: string, name: string, slug: string}|null
     */
    public static function resolve(string $table, string $key): ?array
    {
        if (! isset(self::TABLES[$table]) || trim($key) === '') {
            return null;
        }

        $row = match ($table) {
            'objects' => DB::table('objects')->where('name', $key)->first(['name', 'slug']),
            'cometobjects' => is_numeric($key) ? DB::table('cometobjects')->where('id', (int) $key)->first(['id', 'name', 'slug']) : null,
            default => DB::table($table)->where('slug', $key)->first(['name', 'slug']),
        };
        if (! $row) {
            return null;
        }

        return [
            'type' => self::TABLES[$table],
            'table' => $table,
            // the stored key, so a deep-sky object keeps the spelling of objects.name
            'key' => match ($table) {
                'objects' => (string) $row->name,
                'cometobjects' => (string) $row->id,
                default => (string) $row->slug,
            },
            'name' => html_entity_decode((string) $row->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'slug' => (string) ($row->slug ?: ($row->id ?? $row->name)),
        ];
    }

    /**
     * The objects an observer follows, with their name and slug, by name.
     *
     * @return \Illuminate\Support\Collection<int, array{name: string, slug: string}>
     */
    public static function listFor(User $user): \Illuminate\Support\Collection
    {
        return self::where('user_id', $user->id)->get()
            ->groupBy('target_table')
            ->flatMap(function ($follows, string $table) {
                $keys = $follows->pluck('target_key')->all();
                $column = match ($table) {
                    'objects' => 'name',
                    'cometobjects' => 'id',
                    default => 'slug',
                };

                return DB::table($table)->whereIn($column, $keys)->get(['name', 'slug', ...($table === 'cometobjects' ? ['id'] : [])])
                    ->map(fn ($row) => [
                        'name' => html_entity_decode((string) $row->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'slug' => (string) ($row->slug ?: ($row->id ?? $row->name)),
                    ]);
            })
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public static function isFollowedBy(?User $user, string $table, string $key): bool
    {
        return $user !== null && self::where('user_id', $user->id)
            ->where('target_table', $table)
            ->where('target_key', $key)
            ->exists();
    }

    /**
     * Limit observations to those of the given follows.
     *
     * @param  iterable<ObjectFollow>  $follows
     */
    public static function constrainObservations(Builder $query, iterable $follows): Builder
    {
        return $query->where(function (Builder $query) use ($follows) {
            $any = false;
            foreach (collect($follows)->groupBy(fn (ObjectFollow $follow) => $follow->target_type.'|'.$follow->target_table) as $group) {
                $any = true;
                $query->orWhere(fn (Builder $q) => $q->where('observations.target_type', $group->first()->target_type)
                    ->where('observations.target_table', $group->first()->target_table)
                    ->whereIn('observations.target_key', $group->pluck('target_key')->all()));
            }
            if (! $any) {
                $query->whereRaw('1 = 0');
            }
        });
    }

    /** The follows of a deep-sky object go to the object it was renamed to or merged into. */
    public static function moveDeepskyObject(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        $already = self::where('target_table', 'objects')->where('target_key', $to)->pluck('user_id');
        self::where('target_table', 'objects')->where('target_key', $from)->whereIn('user_id', $already)->delete();
        self::where('target_table', 'objects')->where('target_key', $from)->update(['target_key' => $to]);
    }

    public static function forgetDeepskyObject(string $name): void
    {
        self::where('target_table', 'objects')->where('target_key', $name)->delete();
    }
}
