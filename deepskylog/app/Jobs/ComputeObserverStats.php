<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Observation;
use App\Models\User;

use Illuminate\Support\Arr;

class ComputeObserverStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const SITE_TOTALS_KEY = 'observer_stats:site_totals';

    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle()
    {
        $cacheKey = 'observer_stats:' . $this->userId;
        $inProgressKey = 'observer_stats_in_progress:' . $this->userId;

        try {
            $user = User::find($this->userId);
            if (!$user) {
                Cache::forget($inProgressKey);
                return;
            }

            // The totals of all observers are the same on every observer page,
            // so they are computed once for all of them.
            $stats = array_merge(self::siteTotals(), self::categoryCounts('Your', function ($q) use ($user) {
                return $q->where(function ($q) use ($user) {
                    $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
                });
            }));

            // Precompute per-month series (user-specific) for charts to avoid runtime queries
            $months = range(1, 12);
            $zeroMonths = array_fill(0, 12, 0);

            // Helper to map grouped results into a months array
            $mapToMonths = function ($grouped) use ($zeroMonths) {
                $arr = $zeroMonths;
                foreach ($grouped as $r) {
                    $m = (int) $r->month;
                    if ($m >= 1 && $m <= 12) {
                        $arr[$m - 1] = (int) $r->cnt;
                    }
                }
                return $arr;
            };

            // User-scoped grouped queries
            $month = $this->datePart('month', 'observed_on');
            $year = $this->datePart('year', 'observed_on');
            $observerWhere = function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)->orWhere('observer_username', $user->username);
            };

            $deepskyGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->where('target_type', Observation::TYPE_DEEPSKY)
                ->where($observerWhere)
                ->groupByRaw($month)
                ->get();

            $doubleGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->where('target_table', 'objects')
                ->where($observerWhere)
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->groupByRaw($month)
                ->get();

            $cometGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->where('target_type', Observation::TYPE_COMET)
                ->where($observerWhere)
                ->groupByRaw($month)
                ->get();

            $planetsGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->where('target_table', 'planets')
                ->where('target_key', '!=', 'sun')
                ->where($observerWhere)
                ->groupByRaw($month)
                ->get();

            $sunGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->where('target_table', 'planets')
                ->where('target_key', 'sun')
                ->where($observerWhere)
                ->groupByRaw($month)
                ->get();

            $moonGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->whereIn('target_table', ['moons', 'lunar_features'])
                ->where($observerWhere)
                ->groupByRaw($month)
                ->get();

            $asteroidsGrouped = DB::table('observations')
                ->selectRaw($month . ' as month, count(*) as cnt')
                ->where('target_table', 'asteroids')
                ->where($observerWhere)
                ->groupByRaw($month)
                ->get();

            $deepskyMonths = $mapToMonths($deepskyGrouped);
            $doubleMonths = $mapToMonths($doubleGrouped);
            $cometMonths = $mapToMonths($cometGrouped);
            $planetsMonths = $mapToMonths($planetsGrouped);
            $sunMonths = $mapToMonths($sunGrouped);
            $moonMonths = $mapToMonths($moonGrouped);
            $asteroidsMonths = $mapToMonths($asteroidsGrouped);

            for ($i = 0; $i < 12; $i++) {
                $deepskyMonths[$i] = max(0, $deepskyMonths[$i] - $doubleMonths[$i]);
            }

            // Per-year series
            $firstYear = DB::table('observations')
                ->where($observerWhere)
                ->min(DB::raw($year));

            if (is_null($firstYear)) {
                $firstYear = intval(date('Y'));
            }
            $lastYear = intval(date('Y'));
            $years = range($firstYear, $lastYear);

            $mapToYears = function ($grouped, $firstYear, $lastYear) {
                $len = $lastYear - $firstYear + 1;
                $arr = array_fill(0, $len, 0);
                foreach ($grouped as $r) {
                    $y = (int) $r->year;
                    if ($y >= $firstYear && $y <= $lastYear) {
                        $arr[$y - $firstYear] = (int) $r->cnt;
                    }
                }
                return $arr;
            };

            $deepskyYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->where('target_type', Observation::TYPE_DEEPSKY)
                ->where($observerWhere)
                ->groupByRaw($year)
                ->get();

            $doubleYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->where('target_table', 'objects')
                ->where($observerWhere)
                ->whereIn('target_name_normalized', function ($q) {
                    $q->select('name_normalized')->from('objects')->where('type', 'DS');
                })->groupByRaw($year)
                ->get();

            $cometYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->where('target_type', Observation::TYPE_COMET)
                ->where($observerWhere)
                ->groupByRaw($year)
                ->get();

            $planetsYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->where('target_table', 'planets')
                ->where('target_key', '!=', 'sun')
                ->where($observerWhere)
                ->groupByRaw($year)
                ->get();

            $sunYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->where('target_table', 'planets')
                ->where('target_key', 'sun')
                ->where($observerWhere)
                ->groupByRaw($year)
                ->get();

            $moonYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->whereIn('target_table', ['moons', 'lunar_features'])
                ->where($observerWhere)
                ->groupByRaw($year)
                ->get();

            $asteroidsYearGrouped = DB::table('observations')
                ->selectRaw($year . ' as year, count(*) as cnt')
                ->where('target_table', 'asteroids')
                ->where($observerWhere)
                ->groupByRaw($year)
                ->get();

            $deepskyYears = $mapToYears($deepskyYearGrouped, $firstYear, $lastYear);
            $doubleYears = $mapToYears($doubleYearGrouped, $firstYear, $lastYear);
            $cometYears = $mapToYears($cometYearGrouped, $firstYear, $lastYear);
            $planetsYears = $mapToYears($planetsYearGrouped, $firstYear, $lastYear);
            $sunYears = $mapToYears($sunYearGrouped, $firstYear, $lastYear);
            $moonYears = $mapToYears($moonYearGrouped, $firstYear, $lastYear);
            $asteroidsYears = $mapToYears($asteroidsYearGrouped, $firstYear, $lastYear);

            for ($i = 0; $i < count($deepskyYears); $i++) {
                $deepskyYears[$i] = max(0, $deepskyYears[$i] - $doubleYears[$i]);
            }

            $stats['perMonth'] = [
                'deepsky' => $deepskyMonths,
                'comets' => $cometMonths,
                'double' => $doubleMonths,
                'planets' => $planetsMonths,
                'sun' => $sunMonths,
                'moon' => $moonMonths,
                'asteroids' => $asteroidsMonths,
            ];

            $stats['perYear'] = [
                'x_axis' => $years,
                'deepsky' => $deepskyYears,
                'comets' => $cometYears,
                'double' => $doubleYears,
                'planets' => $planetsYears,
                'sun' => $sunYears,
                'moon' => $moonYears,
                'asteroids' => $asteroidsYears,
            ];

            // Compute whether user has any liked observations — use a JOIN to avoid loading IDs into PHP
            $hasPopularObservations = DB::table('observation_likes as ol')
                ->join('observations as o', 'o.id', '=', 'ol.observation_id')
                ->where(function ($q) use ($user) {
                    $q->where('o.observer_user_id', $user->id)
                        ->orWhere('o.observer_username', $user->username);
                })
                ->where('ol.observation_type', \App\Models\ObservationLike::TYPE_OBSERVATION)
                ->exists();

            $stats['hasPopularObservations'] = $hasPopularObservations;

            // Cache for 6 hours
            Cache::put($cacheKey, $stats, now()->addHours(6));
        } finally {
            // Ensure we don't leave the in-progress flag set; errors go on to
            // the queue (failed_jobs) or the synchronous caller, which logs them
            Cache::forget($inProgressKey);
        }
    }

    /**
     * The counts of all observers ('{category}Total{Metric}', see
     * categoryCounts()), cached under one key for all observer pages.
     *
     * @return array<string, int>
     */
    public static function siteTotals(): array
    {
        return Cache::remember(self::SITE_TOTALS_KEY, now()->addHours(6), fn () => self::categoryCounts('Total'));
    }

    /**
     * The number of observations, observations of the last year, drawings,
     * drawings of the last year and different objects of each category, as
     * '{category}{prefix}{Metric}' (e.g. 'cometYourDrawingsLastYear'). The
     * deep-sky counts leave out the double stars, which are counted apart.
     *
     * @param  \Closure|null  $scope  restricts the observations (to one observer)
     * @return array<string, int>
     */
    private static function categoryCounts(string $prefix, ?\Closure $scope = null): array
    {
        $counts = [];
        foreach (self::categoryQueries() as $category => $query) {
            $base = fn () => $scope ? $scope($query()) : $query();
            $key = $category.$prefix;

            $counts[$key.'Observations'] = (int) $base()->count();
            $counts[$key.'ObservationsLastYear'] = (int) $base()->where('observed_on', '>=', now()->subYear())->count();
            $counts[$key.'Drawings'] = (int) $base()->where('has_drawing', 1)->count();
            $counts[$key.'DrawingsLastYear'] = (int) $base()->where('has_drawing', 1)->where('observed_on', '>=', now()->subYear())->count();
            $counts[$key.'Unique'] = Observation::countDifferentObjects($base());
        }

        foreach (['Observations', 'ObservationsLastYear', 'Drawings', 'DrawingsLastYear', 'Unique'] as $metric) {
            $counts['deepsky'.$prefix.$metric] = max(0, $counts['deepsky'.$prefix.$metric] - $counts['double'.$prefix.$metric]);
        }

        return $counts;
    }

    /**
     * The observations of each category of the observer page, as a closure
     * that returns a fresh query. The deep-sky observations include the
     * double stars, which the counts subtract.
     *
     * @return array<string, \Closure>
     */
    public static function categoryQueries(): array
    {
        $doubleStars = fn ($q) => $q->select('name_normalized')->from('objects')->where('type', 'DS');

        return [
            'double' => fn () => DB::table('observations')->where('target_table', 'objects')->whereIn('target_name_normalized', $doubleStars),
            'deepsky' => fn () => Observation::deepsky(),
            'all' => fn () => Observation::query(),
            'comet' => fn () => Observation::comets(),
            'planets' => fn () => DB::table('observations')->where('target_table', 'planets')->where('target_key', '!=', 'sun'),
            'sun' => fn () => DB::table('observations')->where('target_table', 'planets')->where('target_key', 'sun'),
            'moon' => fn () => DB::table('observations')->whereIn('target_table', ['moons', 'lunar_features']),
            'asteroids' => fn () => DB::table('observations')->where('target_table', 'asteroids'),
        ];
    }

    /**
     * The SQL expression for the year or month of a date column: MySQL has
     * YEAR() and MONTH(), SQLite (the tests) only strftime().
     */
    private function datePart(string $part, string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return $part === 'year'
                ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
                : "CAST(strftime('%m', {$column}) AS INTEGER)";
        }

        return $part === 'year' ? "YEAR({$column})" : "MONTH({$column})";
    }
}
