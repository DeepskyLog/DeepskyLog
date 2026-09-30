<?php

namespace App\Services;

use App\Models\Observation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Observation statistics of deep-sky objects for object tables, taken from the
 * unified `observations` table (never from the legacy database).
 *
 * The statistics are kept per object in object_observation_stats (all
 * observers) and object_observation_stats_by_observer (one observer), which
 * the tables join on the object's name: aggregating the observations of every
 * object a table sorts or filters on took seconds. Only observed objects have
 * a row; the expressions give 0 and null for the others, as the aggregates on
 * the observations did. Dates use the Ymd format the tables' fields and the
 * exports expect.
 *
 * ObservationObserver refreshes the rows of an observation's object when it is
 * saved or deleted; code that changes observations without events (a mass
 * update renaming an object) calls refreshObjects(). rebuild() (the
 * observations:rebuild-object-stats command) recalculates everything.
 */
class ObjectObservationStatistics
{
    public const TABLE = 'object_observation_stats';

    public const OBSERVER_TABLE = 'object_observation_stats_by_observer';

    /** Aliases of the joined statistics tables. */
    private const ALL = 'observation_stats';

    private const MINE = 'my_observation_stats';

    /** The columns of TABLE and OBSERVER_TABLE, in the order aggregate() selects them. */
    private const COLUMNS = ['object_name', 'observations', 'drawings', 'last_observed_on', 'last_drawing_on'];

    private const OBSERVER_COLUMNS = ['object_name', 'observer_username', 'observations', 'drawings', 'last_observed_on', 'last_drawing_on'];

    /**
     * Join the statistics of the object in $objectName: all observers, and the
     * observer $username (none for a guest).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|QueryBuilder  $query
     * @param  string  $objectName  column holding the object's name
     */
    public static function join($query, ?string $username, string $objectName = 'objects.name'): void
    {
        $query->leftJoin(self::TABLE . ' as ' . self::ALL, self::ALL . '.object_name', '=', $objectName)
            ->leftJoin(self::OBSERVER_TABLE . ' as ' . self::MINE, function ($join) use ($objectName, $username) {
                $join->on(self::MINE . '.object_name', '=', $objectName)
                    ->where(self::MINE . '.observer_username', '=', $username ?? '');
            });
    }

    /**
     * The SQL expressions of the statistics, keyed by their column alias. They
     * need the tables of join().
     *
     * @return array<string, string>
     */
    public static function expressions(): array
    {
        $ymd = fn(string $date): string => DB::getDriverName() === 'sqlite'
            ? "strftime('%Y%m%d', {$date})"
            : "DATE_FORMAT({$date}, '%Y%m%d')";

        $expressions = [
            'total_observations' => 'COALESCE(' . self::ALL . '.observations, 0)',
            'total_drawings' => 'COALESCE(' . self::ALL . '.drawings, 0)',
            'your_observations' => 'COALESCE(' . self::MINE . '.observations, 0)',
            'your_drawings' => 'COALESCE(' . self::MINE . '.drawings, 0)',
            'last_seen_date' => $ymd(self::ALL . '.last_observed_on'),
            'your_last_seen_date' => $ymd(self::MINE . '.last_observed_on'),
            'last_drawing_date' => $ymd(self::ALL . '.last_drawing_on'),
            'your_last_drawing_date' => $ymd(self::MINE . '.last_drawing_on'),
        ];

        // Aliases matching the PowerGrid field names, so their sorting works.
        $expressions['seen'] = $expressions['total_observations'];
        $expressions['last_seen'] = $expressions['your_last_seen_date'];

        return $expressions;
    }

    /**
     * The statistics as a select list fragment ("expr as alias, ..."). It
     * needs the tables of join().
     */
    public static function selectList(): string
    {
        return collect(self::expressions())
            ->map(fn(string $expression, string $alias): string => "{$expression} as {$alias}")
            ->implode(', ');
    }

    /**
     * Recalculate the statistics of these objects: those of all observers
     * together, and those of the observers in $usernames, or of every observer
     * when null.
     *
     * @param  iterable<string|null>  $objectNames
     * @param  iterable<string|null>|null  $usernames
     */
    public static function refreshObjects(iterable $objectNames, ?iterable $usernames = null): void
    {
        $objectNames = self::distinctNames($objectNames);
        $usernames = $usernames === null ? null : self::distinctNames($usernames, true);

        foreach ($objectNames as $objectName) {
            DB::transaction(function () use ($objectName, $usernames) {
                $totals = self::aggregate(self::observationsOf($objectName))->first();
                self::store(self::TABLE, ['object_name' => $objectName], $totals);

                if ($usernames === null) {
                    DB::table(self::OBSERVER_TABLE)->where('object_name', $objectName)->delete();
                    DB::table(self::OBSERVER_TABLE)->insertUsing(
                        self::OBSERVER_COLUMNS,
                        self::aggregate(self::observationsOf($objectName), true)
                    );

                    return;
                }

                foreach ($usernames as $username) {
                    $row = self::aggregate(self::observationsOf($objectName)->where('observer_username', $username))->first();
                    self::store(self::OBSERVER_TABLE, ['object_name' => $objectName, 'observer_username' => $username], $row);
                }
            });
        }
    }

    /**
     * Refresh the statistics an observation counts for, before and after a
     * change: its object and its observer, the original ones as well when
     * they changed.
     */
    public static function refreshObservation(Observation $observation): void
    {
        $names = [];
        foreach ([$observation->getAttributes(), $observation->getOriginal()] as $attributes) {
            if (($attributes['target_type'] ?? null) === Observation::TYPE_DEEPSKY) {
                $names[] = $attributes['target_name'] ?? null;
            }
        }

        self::refreshObjects($names, [
            $observation->observer_username,
            $observation->getOriginal('observer_username'),
        ]);
    }

    /**
     * Recalculate the statistics of all objects.
     *
     * @return array{objects: int, observers: int} the number of rows written
     */
    public static function rebuild(): array
    {
        DB::transaction(function () {
            DB::table(self::OBSERVER_TABLE)->delete();
            DB::table(self::TABLE)->delete();

            $deepsky = fn(): QueryBuilder => DB::table('observations')
                ->where('target_type', Observation::TYPE_DEEPSKY)
                ->whereNotNull('target_name');

            DB::table(self::TABLE)->insertUsing(self::COLUMNS, self::aggregate($deepsky()));
            DB::table(self::OBSERVER_TABLE)->insertUsing(self::OBSERVER_COLUMNS, self::aggregate($deepsky(), true));
        });

        return [
            'objects' => DB::table(self::TABLE)->count(),
            'observers' => DB::table(self::OBSERVER_TABLE)->count(),
        ];
    }

    /**
     * The deep-sky observations of one object. Matched on target_name, as
     * the tables join on it; for deep-sky objects it equals target_key.
     */
    private static function observationsOf(string $objectName): QueryBuilder
    {
        return DB::table('observations')
            ->where('target_type', Observation::TYPE_DEEPSKY)
            ->where('target_name', $objectName);
    }

    /**
     * The statistics of the observations in $query, per object (and per
     * observer), in the column order of COLUMNS (OBSERVER_COLUMNS).
     */
    private static function aggregate(QueryBuilder $query, bool $perObserver = false): QueryBuilder
    {
        $query->groupBy('target_name');
        $select = 'target_name as object_name';
        if ($perObserver) {
            $query->groupBy('observer_username');
            $select .= ', observer_username';
        }

        return $query->selectRaw($select
            . ', COUNT(*) as observations'
            . ', SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) as drawings'
            . ', MAX(observed_on) as last_observed_on'
            . ', MAX(CASE WHEN has_drawing = 1 THEN observed_on END) as last_drawing_on');
    }

    /**
     * Store the aggregate of one row, or remove the row when nothing is left
     * to count. An upsert, so that two observations of the same object saved
     * at once don't collide on the primary key.
     *
     * @param  array<string, string>  $key
     */
    private static function store(string $table, array $key, ?object $row): void
    {
        if ($row === null || (int) $row->observations === 0) {
            DB::table($table)->where($key)->delete();

            return;
        }

        $values = [
            'observations' => (int) $row->observations,
            'drawings' => (int) $row->drawings,
            'last_observed_on' => $row->last_observed_on,
            'last_drawing_on' => $row->last_drawing_on,
        ];

        DB::table($table)->upsert([$key + $values], array_keys($key), array_keys($values));
    }

    /**
     * @param  iterable<string|null>  $names
     * @return array<int, string>
     */
    private static function distinctNames(iterable $names, bool $allowEmpty = false): array
    {
        $distinct = [];
        foreach ($names as $name) {
            if ($name === null || (!$allowEmpty && $name === '')) {
                continue;
            }
            $distinct[mb_strtolower((string) $name)] = (string) $name;
        }

        return array_values($distinct);
    }
}
