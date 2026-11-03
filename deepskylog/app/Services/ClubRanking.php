<?php

namespace App\Services;

use App\Models\Club;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The ranking of the members of a club in a period (PERIODS): their
 * observations, drawings and different objects (not-seen observations left
 * out), and optionally the objects of a catalog or observing list
 * (ObjectSources) they saw, as the ranking of /observers counts them.
 * Members with the same number share a rank.
 *
 * Cached CACHE_MINUTES per club, members, period and source; new
 * observations show up when the cache expires.
 */
class ClubRanking
{
    public const CACHE_MINUTES = 10;

    /** The periods, the first is the default. */
    public const PERIODS = ['year', '12m', 'all'];

    /** The columns to rank by, the first is the default. */
    public const SORTS = ['objects', 'observations', 'drawings', 'source'];

    /**
     * @param  array<string, mixed>|null  $source  of ObjectSources, null for none
     * @return array{rows: list<array{user_id: int, name: string, slug: string, observations: int, drawings: int, objects: int, source: int, rank: int}>, source_total: int}
     */
    public static function for(Club $club, string $period, ?array $source, string $sort): array
    {
        $period  = in_array($period, self::PERIODS, true) ? $period : self::PERIODS[0];
        $sort    = in_array($sort, self::SORTS, true) && ($sort !== 'source' || $source !== null) ? $sort : self::SORTS[0];
        $members = $club->members()->orderBy('users.id')->get(['users.id', 'users.name', 'users.username', 'users.slug']);

        $key    = 'club_ranking:'.$club->id.':'.md5($members->pluck('id')->implode(',')).':'.$period.':'.($source['key'] ?? '');
        $counts = Cache::remember($key, now()->addMinutes(self::CACHE_MINUTES), fn () => self::compute($members, self::since($period), $source));

        $rows = $members->map(function (User $member) use ($counts) {
            $username = mb_strtolower((string) $member->username);

            return [
                'user_id'      => (int) $member->id,
                'name'         => html_entity_decode((string) $member->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'slug'         => (string) ($member->slug ?: $member->username),
                'observations' => (int) ($counts['observations'][$username] ?? 0),
                'drawings'     => (int) ($counts['drawings'][$username] ?? 0),
                'objects'      => (int) ($counts['objects'][$username] ?? 0),
                'source'       => (int) ($counts['source'][$username] ?? 0),
            ];
        });

        return ['rows' => self::rank($rows, $sort), 'source_total' => (int) $counts['source_total']];
    }

    /** The first day of the period, null for all observations. */
    public static function since(string $period): ?string
    {
        return match ($period) {
            'year'  => Carbon::today()->startOfYear()->toDateString(),
            '12m'   => Carbon::today()->subYear()->toDateString(),
            default => null,
        };
    }

    /**
     * @param  Collection<int, User>  $members
     * @return array{observations: array<string, int>, drawings: array<string, int>, objects: array<string, int>, source: array<string, int>, source_total: int}
     */
    private static function compute(Collection $members, ?string $since, ?array $source): array
    {
        $usernames = $members->pluck('username')->filter()->values()->all();
        $result    = ['observations' => [], 'drawings' => [], 'objects' => [], 'source' => [], 'source_total' => 0];
        if ($usernames === []) {
            return $result;
        }

        $inPeriod = fn (Builder $query) => $query
            ->whereIn('observations.observer_username', $usernames)
            ->when($since !== null, fn (Builder $q) => $q->where('observations.observed_on', '>=', $since));
        $seen = fn (Builder $query) => $query->whereNotExists(fn (Builder $details) => $details->selectRaw('1')
            ->from('deepsky_observation_details')
            ->whereColumn('deepsky_observation_details.observation_id', 'observations.id')
            ->where('deepsky_observation_details.visibility', Observation::VISIBILITY_NOT_SEEN));

        foreach (DB::table('observations')->tap($inPeriod)
            ->groupBy('observations.observer_username')
            ->select('observations.observer_username')
            ->selectRaw('COUNT(*) as observations_count')
            ->selectRaw('SUM(CASE WHEN has_drawing = 1 THEN 1 ELSE 0 END) as drawings_count')
            ->get() as $row) {
            $username                          = mb_strtolower((string) $row->observer_username);
            $result['observations'][$username] = ($result['observations'][$username] ?? 0) + (int) $row->observations_count;
            $result['drawings'][$username]     = ($result['drawings'][$username] ?? 0)     + (int) $row->drawings_count;
        }

        foreach (DB::table('observations')->tap($inPeriod)->tap($seen)
            ->groupBy('observations.observer_username')
            ->selectRaw('observations.observer_username, COUNT(DISTINCT '.Observation::DIFFERENT_OBJECT_SQL.') as objects_count')
            ->get() as $row) {
            $username                     = mb_strtolower((string) $row->observer_username);
            $result['objects'][$username] = ($result['objects'][$username] ?? 0) + (int) $row->objects_count;
        }

        if ($source !== null) {
            [$result['source'], $result['source_total']] = self::sourceCounts($source, $inPeriod, $seen);
        }

        return $result;
    }

    /**
     * The different objects of the source each member saw, and the number of
     * objects of the source.
     *
     * @return array{0: array<string, int>, 1: int}
     */
    private static function sourceCounts(array $source, \Closure $inPeriod, \Closure $seen): array
    {
        $observations = DB::table('observations')
            ->tap($inPeriod)->tap($seen)
            ->where('observations.target_type', Observation::TYPE_DEEPSKY)
            ->groupBy('observations.observer_username');

        if (($source['type'] ?? null) === 'list') {
            $names = DB::table('observing_list_items')->where('observing_list_id', $source['list_id'])
                ->pluck('object_name')->filter()->unique()->values();
            if ($names->isEmpty()) {
                return [[], 0];
            }
            $rows = $observations->whereIn('observations.target_name', $names->all())
                ->selectRaw('observations.observer_username, COUNT(DISTINCT observations.target_name) as observed_count')
                ->get();
            $total = $names->map(fn ($name) => mb_strtolower((string) $name))->unique()->count();
        } else {
            $catalog = (string) ($source['catalog'] ?? '');
            $rows    = $observations->join('objectnames', 'objectnames.objectname', '=', 'observations.target_name')
                ->where('objectnames.catalog', $catalog)
                ->selectRaw('observations.observer_username, COUNT(DISTINCT objectnames.catindex) as observed_count')
                ->get();
            $total = (int) DB::table('objectnames')->where('catalog', $catalog)->distinct('catindex')->count('catindex');
        }

        $counts = [];
        foreach ($rows as $row) {
            $username          = mb_strtolower((string) $row->observer_username);
            $counts[$username] = ($counts[$username] ?? 0) + (int) $row->observed_count;
        }

        return [$counts, $total];
    }

    /**
     * Highest first, then by name; the same number shares a rank.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private static function rank(Collection $rows, string $sort): array
    {
        $sorted = $rows->sort(fn (array $a, array $b) => [$b[$sort], Str::lower($a['name'])] <=> [$a[$sort], Str::lower($b['name'])])->values();

        $ranked   = [];
        $previous = null;
        foreach ($sorted as $index => $row) {
            $row['rank'] = $previous !== null && $previous[$sort] === $row[$sort] ? $previous['rank'] : $index + 1;
            $ranked[]    = $previous = $row;
        }

        return $ranked;
    }
}
