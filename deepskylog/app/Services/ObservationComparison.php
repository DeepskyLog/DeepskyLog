<?php

namespace App\Services;

use App\Models\Observation;
use deepskylog\AstronomyLibrary\Magnitude;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * The "compare my observations" filter of the advanced object search: the
 * deep-sky objects an observer saw with one instrument, at one location or
 * under a poor (dark) sky, but not yet with another instrument, at another
 * location or under a dark (poor) sky. Observations of an object that was
 * not seen count for neither side.
 */
class ObservationComparison
{
    public const BY_INSTRUMENT = 'instrument';

    public const BY_LOCATION = 'location';

    public const BY_SKY = 'sky';

    public const BY = [self::BY_INSTRUMENT, self::BY_LOCATION, self::BY_SKY];

    /** Seen under a sky with an SQM below the threshold, not yet under a darker one. */
    public const SKY_POOR_NOT_DARK = 'poor_not_dark';

    /** Seen under a sky with an SQM of the threshold or more, not yet under a poorer one. */
    public const SKY_DARK_NOT_POOR = 'dark_not_poor';

    public const SKY_DIRECTIONS = [self::SKY_POOR_NOT_DARK, self::SKY_DARK_NOT_POOR];

    public const MIN_SQM = 10.0;

    public const MAX_SQM = 22.0;

    /**
     * Restrict the search_index rows in $rows to the deep-sky objects that
     * $username saw with $seen but not yet with $notSeen (an instrument or
     * location id), or for BY_SKY, on the side of the SQM $sqm that
     * $direction gives first but not on the other. Nothing matches for a
     * guest or an incomplete comparison.
     */
    public static function restrict(
        QueryBuilder $rows,
        ?string $username,
        string $by,
        string $seen = '',
        string $notSeen = '',
        string $sqm = '',
        string $direction = self::SKY_POOR_NOT_DARK
    ): void {
        $conditions = self::conditions($by, $seen, $notSeen, $sqm, $direction);
        if ($username === null || $username === '' || $conditions === null) {
            $rows->whereRaw('1 = 0');

            return;
        }

        [$seenCondition, $notSeenCondition] = $conditions;

        // Both sides are fetched once instead of as subqueries: MariaDB scans
        // all observations for them, and the results table runs its query
        // several times (both halves of its UNION, the count and the page).
        $names = array_values(array_diff(
            self::seenObjects($username, $seenCondition),
            self::seenObjects($username, $notSeenCondition)
        ));

        $rows->where('source_table', 'objects');
        if ($names === []) {
            $rows->whereRaw('1 = 0');
        } else {
            $rows->whereIn('name', $names);
        }
    }

    /**
     * The conditions on the observations of both sides, or null when the
     * comparison is incomplete.
     *
     * @return array{0: \Closure, 1: \Closure}|null
     */
    private static function conditions(string $by, string $seen, string $notSeen, string $sqm, string $direction): ?array
    {
        if ($by === self::BY_INSTRUMENT || $by === self::BY_LOCATION) {
            $seenId = (int) $seen;
            $notSeenId = (int) $notSeen;
            if ($seenId <= 0 || $notSeenId <= 0) {
                return null;
            }
            $column = $by === self::BY_INSTRUMENT ? 'observations.instrument_id' : 'observations.location_id';

            return [
                fn (QueryBuilder $q) => $q->where($column, $seenId),
                fn (QueryBuilder $q) => $q->where($column, $notSeenId),
            ];
        }

        if ($by === self::BY_SKY) {
            if (!is_numeric($sqm) || (float) $sqm < self::MIN_SQM || (float) $sqm > self::MAX_SQM) {
                return null;
            }
            $dark = self::darkSkySql((float) $sqm);
            $poorFirst = $direction !== self::SKY_DARK_NOT_POOR;

            return [
                fn (QueryBuilder $q) => self::joinLocation($q)->whereRaw("{$dark[0]} = ?", [...$dark[1], $poorFirst ? 0 : 1]),
                fn (QueryBuilder $q) => self::joinLocation($q)->whereRaw("{$dark[0]} = ?", [...$dark[1], $poorFirst ? 1 : 0]),
            ];
        }

        return null;
    }

    /**
     * The names of the deep-sky objects $username saw in the observations
     * that meet $condition.
     *
     * @return list<string>
     */
    private static function seenObjects(string $username, \Closure $condition): array
    {
        $query = DB::table('observations')
            ->select('observations.target_name')
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->where('observations.observer_username', $username)
            ->whereNotNull('observations.target_name')
            // Always true then, but it has MariaDB take the index on the
            // observer (idx_obs_observer_different_object) instead of scanning
            // all deep-sky observations: 0.1 s instead of 0.5 s
            ->whereNotNull('observations.different_object_key')
            // A join on its key: as NOT EXISTS, MariaDB scans the whole table
            ->leftJoin('deepsky_observation_details', 'deepsky_observation_details.observation_id', '=', 'observations.id')
            ->where(function (QueryBuilder $q) {
                $q->whereNull('deepsky_observation_details.visibility')
                    ->orWhere('deepsky_observation_details.visibility', '<>', Observation::VISIBILITY_NOT_SEEN);
            });
        $condition($query);

        return $query->distinct()->pluck('observations.target_name')->all();
    }

    private static function joinLocation(QueryBuilder $query): QueryBuilder
    {
        return $query->leftJoin('locations', 'locations.id', '=', 'observations.location_id');
    }

    /**
     * SQL that is 1 when the sky of an observation was at least as dark as
     * the SQM $sqm, 0 when it was brighter, and NULL when it is unknown, with
     * its bindings. The sky is that of the observation, else its location's,
     * each by its SQM, else its naked eye limiting magnitude compared with
     * the one the SQM $sqm gives.
     *
     * @return array{0: string, 1: list<float>}
     */
    private static function darkSkySql(float $sqm): array
    {
        $nelm = Magnitude::sqmToNelm($sqm);
        $min = self::MIN_SQM;
        $compare = fn (string $column): string => "CASE WHEN {$column} >= ? THEN 1 ELSE 0 END";

        $sql = 'CASE'
            . " WHEN observations.sqm >= {$min} THEN " . $compare('observations.sqm')
            . ' WHEN observations.limiting_magnitude > 0 AND observations.limiting_magnitude <= 8 THEN ' . $compare('observations.limiting_magnitude')
            . " WHEN locations.skyBackground >= {$min} THEN " . $compare('locations.skyBackground')
            . ' WHEN locations.limitingMagnitude > 0 AND locations.limitingMagnitude <= 8 THEN ' . $compare('locations.limitingMagnitude')
            . ' END';

        return [$sql, [$sqm, $nelm, $sqm, $nelm]];
    }
}
