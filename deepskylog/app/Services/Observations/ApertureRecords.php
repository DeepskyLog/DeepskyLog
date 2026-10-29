<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\SuspectObservation;
use App\Services\LocationSkyHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Records of the deep-sky objects reported seen: the smallest aperture and
 * the brightest sky (SQM and NELM separately) with which an object was seen
 * (object page), and the faintest objects seen up to an aperture (ranking
 * page /records/aperture). They answer "can I see this with my 4-inch?" from
 * real observations instead of a calculation.
 *
 * An observation counts when the object was seen (not VISIBILITY_NOT_SEEN)
 * and the suspect-observation check has no open finding of SUSPECT_REASONS
 * for it (a dismissed one counts), so impossible records are left out. That
 * check skips observations without a sky (and the types it cannot judge), so
 * an observation also only counts when the object's magnitude is within
 * reach of a star limit of the aperture under a very dark sky
 * (plausibleSql()): magnitudes of 20 to 22 (central stars of planetary
 * nebulae) "seen" with 200 mm would otherwise top the ranking. The naked eye
 * counts as an aperture of NAKED_EYE_MM, whatever its instrument stores (7
 * to 30 mm); another instrument below MIN_APERTURE_MM is a typing error (6
 * for 6 inch). The sky is the SQM or NELM of the observation, else,
 * when it has neither, that of its location; values outside
 * LocationSkyHistory::SQM_RANGE / NELM_RANGE are typing errors.
 */
class ApertureRecords
{
    /** instrument_types.name of the naked eye (the id differs between databases). */
    public const NAKED_EYE_TYPE = 'Naked Eye';

    /** The aperture of the naked eye: the pupil of a dark-adapted eye. */
    public const NAKED_EYE_MM = 7;

    /** A smaller aperture of an instrument other than the naked eye is a typing error. */
    public const MIN_APERTURE_MM = 15;

    /**
     * The plausible faintest magnitude of an aperture D is PLAUSIBLE_NELM +
     * 5 log10(D / NAKED_EYE_MM) + PLAUSIBLE_MARGIN: the star limit under a
     * very dark sky with the gain of a high magnification and some room (12.8
     * for 50 mm, 14.3 for 100 mm, 15.8 for 200 mm). An extended object is harder than a star
     * of its magnitude, so a fainter one cannot have been seen.
     */
    public const PLAUSIBLE_NELM = 7.5;

    public const PLAUSIBLE_MARGIN = 1.0;

    /** The findings of the suspect-observation check that keep an observation out. */
    public const SUSPECT_REASONS = [SuspectObservation::REASON_BELOW_HORIZON, SuspectObservation::REASON_TOO_FAINT];

    /**
     * The aperture limits of the ranking: nominal mm => [largest aperture in
     * mm, size in inches for the label]. The largest aperture leaves room for
     * the inch sizes (a 4" is 101.6 mm). 0 is the naked eye.
     */
    public const LIMITS = [
        0 => [self::NAKED_EYE_MM, null],
        50 => [51, 2],
        80 => [80, 3],
        100 => [102, 4],
        150 => [153, 6],
        200 => [204, 8],
    ];

    public const DEFAULT_LIMIT = 100;

    /**
     * Types left out of the ranking: their magnitude does not say how hard
     * they are to see (the primary of a double star, a variable star or a
     * supernova at its brightest, the stars of a cluster seen one by one), or
     * they have none.
     */
    public const RANKING_EXCLUDED_TYPES = ['DS', 'AA1STAR', 'SNOVA', ...SuspectObservations::FAINT_EXCLUDED_TYPES];

    /** Objects in the ranking. */
    public const RANKING_SIZE = 100;

    /** Observers in the leaderboard of the ranking page. */
    public const OBSERVERS_SIZE = 25;

    /** How long the ranking stays fresh, and then stale while it is recomputed. */
    public const CACHE_SECONDS = [3600, 86400];

    /**
     * The records of one deep-sky object (its canonical name): the seen
     * observation with the smallest aperture and those under the brightest
     * SQM and NELM sky, the first one made when several share it; null when
     * there is none. Each carries record_aperture_mm, record_naked_eye,
     * record_sqm, record_nelm and record_sky_from_location.
     *
     * @return array{aperture: ?Observation, sqm: ?Observation, nelm: ?Observation}
     */
    public function forObject(string $objectName): array
    {
        $records = [];
        foreach (['aperture' => 'record_aperture_mm', 'sqm' => 'record_sqm', 'nelm' => 'record_nelm'] as $key => $column) {
            $records[$key] = $this->seen()
                ->where('observations.target_key', $objectName)
                ->when($key !== 'aperture', fn (Builder $query) => $query->whereRaw(self::skySql($key).' IS NOT NULL'))
                ->orderBy($column)
                ->orderBy('observations.observed_on')
                ->orderBy('observations.id')
                ->first();
        }

        return $records;
    }

    /**
     * The faintest objects (by objects.mag) seen with at most the aperture of
     * a limit of LIMITS, each with the observation that saw it with the
     * smallest aperture.
     *
     * @return list<array{rank: int, name: string, slug: string, type: string, mag: float, observation_id: int, aperture_mm: float, naked_eye: bool, observer: string, observed_on: string}>
     */
    public function ranking(int $limit): array
    {
        $limit = self::isLimit($limit) ? $limit : self::DEFAULT_LIMIT;

        return Cache::flexible("aperture_records:objects:{$limit}", self::CACHE_SECONDS, fn () => $this->computeRanking($limit));
    }

    /**
     * The observers by the faintest object they saw with at most the aperture
     * of a limit, each with that observation (the smallest aperture, then the
     * first made, of their observations of their faintest magnitude).
     *
     * @return list<array{rank: int, observer: string, name: string, slug: string, type: string, mag: float, observation_id: int, aperture_mm: float, naked_eye: bool, observed_on: string}>
     */
    public function observers(int $limit): array
    {
        $limit = self::isLimit($limit) ? $limit : self::DEFAULT_LIMIT;

        return Cache::flexible("aperture_records:observers:{$limit}", self::CACHE_SECONDS, fn () => $this->computeObservers($limit));
    }

    public static function isLimit(mixed $limit): bool
    {
        return is_numeric($limit) && array_key_exists((int) $limit, self::LIMITS) && (string) (int) $limit === (string) $limit;
    }

    /** The label of a limit of LIMITS, in inches or millimetres. */
    public static function limitLabel(int $limit, bool $inches): string
    {
        if ($limit === 0 || ! isset(self::LIMITS[$limit])) {
            return __('Naked eye');
        }

        return __('up to :size', ['size' => $inches ? self::LIMITS[$limit][1].'″' : $limit.' mm']);
    }

    /** An aperture of a record as the viewer reads it. */
    public static function apertureLabel(mixed $apertureMm, bool $nakedEye, bool $inches): ?string
    {
        return $nakedEye ? __('Naked eye') : DrawingComparison::aperture($apertureMm, $inches);
    }

    /**
     * The seen observations of deep-sky objects that the suspect-observation
     * check does not doubt, with their aperture and sky.
     *
     * @return Builder<Observation>
     */
    private function seen(): Builder
    {
        return $this->restrict(Observation::query())
            ->select('observations.*')
            ->selectRaw(self::apertureSql().' AS record_aperture_mm')
            ->selectRaw("CASE WHEN instrument_types.name = '".self::NAKED_EYE_TYPE."' THEN 1 ELSE 0 END AS record_naked_eye")
            ->selectRaw(self::skySql('sqm').' AS record_sqm')
            ->selectRaw(self::skySql('nelm').' AS record_nelm')
            ->selectRaw('CASE WHEN '.self::ownSkySql().' THEN 0 ELSE 1 END AS record_sky_from_location');
    }

    /**
     * @template T of Builder|QueryBuilder
     *
     * @param  T  $query
     * @return T
     */
    private function restrict(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query
            ->join('instruments', 'instruments.id', '=', 'observations.instrument_id')
            ->leftJoin('instrument_types', 'instrument_types.id', '=', 'instruments.instrument_type_id')
            ->leftJoin('locations', 'locations.id', '=', 'observations.location_id')
            ->join('objects', 'objects.name', '=', 'observations.target_key')
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->whereRaw(self::apertureSql().' IS NOT NULL')
            ->whereRaw(self::plausibleSql())
            ->whereNotExists(fn (QueryBuilder $details) => $details->selectRaw('1')
                ->from('deepsky_observation_details')
                ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
                ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN))
            ->whereNotExists(fn (QueryBuilder $suspect) => $suspect->selectRaw('1')
                ->from('suspect_observations')
                ->whereColumn('suspect_observations.observation_id', 'observations.id')
                ->whereIn('suspect_observations.reason', self::SUSPECT_REASONS)
                ->whereNull('suspect_observations.dismissed_at'));
    }

    /** The aperture in mm, the naked eye as NAKED_EYE_MM; null when unknown. */
    private static function apertureSql(): string
    {
        return "CASE WHEN instrument_types.name = '".self::NAKED_EYE_TYPE."' THEN ".self::NAKED_EYE_MM
            .' WHEN instruments.aperture_mm >= '.self::MIN_APERTURE_MM.' THEN instruments.aperture_mm END';
    }

    /** Whether the object's magnitude (if known) is within reach of the aperture. */
    private static function plausibleSql(): string
    {
        return '(objects.mag >= 99 OR objects.mag = 0 OR objects.mag <= '
            .(self::PLAUSIBLE_NELM + self::PLAUSIBLE_MARGIN).' + 5 * LOG10(('.self::apertureSql().') / '.self::NAKED_EYE_MM.'))';
    }

    /** Whether the observation has an SQM or NELM of its own. */
    private static function ownSkySql(): string
    {
        [$sqmFrom, $sqmTo] = LocationSkyHistory::SQM_RANGE;
        [$nelmFrom, $nelmTo] = LocationSkyHistory::NELM_RANGE;

        // IS NOT NULL first: NOT (NULL BETWEEN ...) would be NULL, not true
        return "((observations.sqm IS NOT NULL AND observations.sqm BETWEEN {$sqmFrom} AND {$sqmTo})"
            ." OR (observations.limiting_magnitude IS NOT NULL AND observations.limiting_magnitude BETWEEN {$nelmFrom} AND {$nelmTo}))";
    }

    /** The SQM ('sqm') or NELM ('nelm') of the observation, else of its location. */
    private static function skySql(string $kind): string
    {
        [$from, $to] = $kind === 'sqm' ? LocationSkyHistory::SQM_RANGE : LocationSkyHistory::NELM_RANGE;
        [$own, $location] = $kind === 'sqm'
            ? ['observations.sqm', 'locations.skyBackground']
            : ['observations.limiting_magnitude', 'locations.limitingMagnitude'];

        return "CASE WHEN {$own} BETWEEN {$from} AND {$to} THEN {$own}"
            .' WHEN NOT '.self::ownSkySql()." AND {$location} BETWEEN {$from} AND {$to} THEN {$location} END";
    }

    /** The seen observations with at most the aperture of a limit, of objects with a magnitude. */
    private function withinLimit(int $limit): QueryBuilder
    {
        return $this->restrict(DB::table('observations'))
            ->whereRaw(self::apertureSql().' <= ?', [self::LIMITS[$limit][0]])
            ->where('objects.mag', '<', 99)
            ->where('objects.mag', '!=', 0)
            ->whereNotIn('objects.type', self::RANKING_EXCLUDED_TYPES);
    }

    private function computeRanking(int $limit): array
    {
        $names = $this->withinLimit($limit)
            ->groupBy('objects.name')
            ->selectRaw('objects.name AS name, MAX(objects.mag) AS mag')
            ->orderByDesc('mag')
            ->orderBy('name')
            ->limit(self::RANKING_SIZE)
            ->pluck('name');
        if ($names->isEmpty()) {
            return [];
        }

        $first = $this->firstPer($this->withinLimit($limit)->whereIn('objects.name', $names), 'objects.name');

        $rows = [];
        foreach ($names as $name) {
            if (isset($first[$name])) {
                $rows[] = $this->row($first[$name]);
            }
        }

        return self::ranked($rows);
    }

    private function computeObservers(int $limit): array
    {
        $faintest = $this->withinLimit($limit)
            ->groupBy('observations.observer_username')
            ->selectRaw('observations.observer_username AS observer, MAX(objects.mag) AS mag')
            ->orderByDesc('mag')
            ->orderBy('observer')
            ->limit(self::OBSERVERS_SIZE)
            ->get();
        if ($faintest->isEmpty()) {
            return [];
        }

        $query = $this->withinLimit($limit)->where(function (QueryBuilder $pairs) use ($faintest) {
            foreach ($faintest as $row) {
                $pairs->orWhere(fn (QueryBuilder $pair) => $pair
                    ->where('observations.observer_username', $row->observer)
                    ->where('objects.mag', $row->mag));
            }
        });
        $first = $this->firstPer($query, 'observations.observer_username');

        $rows = [];
        foreach ($faintest as $row) {
            if (isset($first[$row->observer])) {
                $rows[] = ['observer' => (string) $row->observer] + $this->row($first[$row->observer]);
            }
        }

        return self::ranked($rows);
    }

    /**
     * The rows, faintest first, with their rank: rows of the same magnitude
     * share it (1, 2, 2, 4).
     */
    private static function ranked(array $rows): array
    {
        $ranked = [];
        foreach ($rows as $i => $row) {
            $rank = $i > 0 && $row['mag'] === $rows[$i - 1]['mag'] ? $ranked[$i - 1]['rank'] : $i + 1;
            $ranked[] = ['rank' => $rank] + $row;
        }

        return $ranked;
    }

    /**
     * The observation with the smallest aperture, then the first made, per
     * value of a column.
     *
     * @return array<string, object>
     */
    private function firstPer(QueryBuilder $query, string $column): array
    {
        $rows = $query
            ->select('observations.id', 'observations.observer_username', 'observations.observed_on', "{$column} AS record_group",
                'objects.name', 'objects.slug', 'objects.type', 'objects.mag')
            ->selectRaw(self::apertureSql().' AS aperture_mm')
            ->selectRaw("CASE WHEN instrument_types.name = '".self::NAKED_EYE_TYPE."' THEN 1 ELSE 0 END AS naked_eye")
            ->orderBy('aperture_mm')
            ->orderBy('observations.observed_on')
            ->orderBy('observations.id')
            ->get();

        $first = [];
        foreach ($rows as $row) {
            $first[(string) $row->record_group] ??= $row;
        }

        return $first;
    }

    private function row(object $row): array
    {
        return [
            'name' => (string) $row->name,
            'slug' => (string) $row->slug,
            'type' => (string) $row->type,
            'mag' => (float) $row->mag,
            'observation_id' => (int) $row->id,
            'aperture_mm' => (float) $row->aperture_mm,
            'naked_eye' => (bool) $row->naked_eye,
            'observer' => (string) $row->observer_username,
            'observed_on' => substr((string) $row->observed_on, 0, 10),
        ];
    }

    /**
     * The observations of the rows of the ranking, keyed by id, for their
     * links (detailUrl()).
     *
     * @param  list<array{observation_id: int}>  ...$rows
     * @return Collection<int, Observation>
     */
    public static function observationsOf(array ...$rows): Collection
    {
        $ids = collect($rows)->flatten(1)->pluck('observation_id')->unique()->values();

        return $ids->isEmpty() ? collect() : Observation::whereIn('id', $ids)->get()->keyBy('id');
    }
}
