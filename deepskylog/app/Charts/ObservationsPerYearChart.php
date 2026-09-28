<?php

namespace App\Charts;

use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;
use marineusde\LarapexCharts\Options\XAxisOption;

/**
 * Class ObservationsPerYearChart
 *
 * This class is responsible for building a line chart that represents the number of observations per year.
 * The chart includes total observations, deep sky observations, and comet observations.
 */
class ObservationsPerYearChart
{
    /**
     * Builds a line chart of observations per year for a given user.
     *
     * This method retrieves the first observation of the user and calculates the number of observations
     * (total, deep sky, and comet) for each year from the year of the first observation to the current year.
     * It then creates a line chart with these data, sets the title, subtitle, and theme of the chart, and returns it.
     *
     * @param  User  $user  The user for whom the chart is to be built.
     * @return OriginalLineChart The built line chart.
     */
    public function build(User $user): OriginalLineChart
    {
        // Prefer cached precomputed per-year series when available
        $stats = Cache::get('observer_stats:' . $user->id);
        if ($stats && isset($stats['perYear'])) {
            $py = $stats['perYear'];
            $x_axis = $py['x_axis'] ?? [];
            $deepsky_observations = array_map('intval', $py['deepsky'] ?? []);
            $comet_observations = array_map('intval', $py['comets'] ?? []);
            $double_observations = array_map('intval', $py['double'] ?? []);
            $planets_observations = array_map('intval', $py['planets'] ?? []);
            $sun_observations = array_map('intval', $py['sun'] ?? []);
            $moon_observations = array_map('intval', $py['moon'] ?? []);

            $observations = [];
            $n = count($x_axis);
            for ($i = 0; $i < $n; $i++) {
                $observations[] = ($deepsky_observations[$i] ?? 0) + ($comet_observations[$i] ?? 0) + ($double_observations[$i] ?? 0) + ($planets_observations[$i] ?? 0) + ($sun_observations[$i] ?? 0) + ($moon_observations[$i] ?? 0);
            }

            $chart = (new OriginalLineChart)
                ->setTitle(__('Number of observations per year: ') . $user->name)
                ->setSubtitle(__('Source: ') . config('app.url'))
                ->addData(__('Total'), $observations)
                ->addData(__('Deepsky'), $deepsky_observations)
                ->addData(__('Comets'), $comet_observations)
                ->setXAxisOption(new XAxisOption($x_axis))
                ->setTheme('dark')
                ->setFontColor('#bbbbbb')
                ->setToolbar(true);

            if (!empty($double_observations)) {
                $chart->addData(__('Double stars'), $double_observations);
            }
            if (!empty($planets_observations)) {
                $chart->addData(__('Planets'), $planets_observations);
            }
            if (!empty($sun_observations)) {
                $chart->addData(__('Sun'), $sun_observations);
            }
            if (!empty($moon_observations)) {
                $chart->addData(__('Moon'), $moon_observations);
            }

            return $chart;
        }
        // Fallback: use bulk GROUP BY queries instead of a per-year loop
        $firstYear = (int) (DB::table('observations')
            ->where('observer_username', $user->username)
            ->min(DB::raw('YEAR(observed_on)')) ?? date('Y'));
        $lastYear = (int) date('Y');

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
            ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_type', 'deepsky')
            ->groupByRaw('YEAR(observed_on)')
            ->get();

        $cometYearGrouped = DB::table('observations')
            ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_type', 'comet')
            ->groupByRaw('YEAR(observed_on)')
            ->get();

        $doubleYearGrouped = collect(); // skip expensive double-star query; will be 0 on cold cache (job will warm it)

        $planetsYearGrouped = DB::table('observations')
            ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_table', 'planets')
            ->whereRaw('LOWER(target_key) != ?', ['sun'])
            ->groupByRaw('YEAR(observed_on)')
            ->get();

        $sunYearGrouped = DB::table('observations')
            ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_table', 'planets')
            ->whereRaw('LOWER(target_key) = ?', ['sun'])
            ->groupByRaw('YEAR(observed_on)')
            ->get();

        $moonYearGrouped = DB::table('observations')
            ->selectRaw('YEAR(observed_on) as year, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->whereIn('target_table', ['moons', 'lunar_features'])
            ->groupByRaw('YEAR(observed_on)')
            ->get();

        $deepsky_observations = $mapToYears($deepskyYearGrouped, $firstYear, $lastYear);
        $comet_observations = $mapToYears($cometYearGrouped, $firstYear, $lastYear);
        $double_observations = $mapToYears($doubleYearGrouped, $firstYear, $lastYear);
        $planets_observations = $mapToYears($planetsYearGrouped, $firstYear, $lastYear);
        $sun_observations = $mapToYears($sunYearGrouped, $firstYear, $lastYear);
        $moon_observations = $mapToYears($moonYearGrouped, $firstYear, $lastYear);

        $x_axis = range($firstYear, $lastYear);
        $n = count($x_axis);
        $observations = [];
        for ($i = 0; $i < $n; $i++) {
            $observations[] = ($deepsky_observations[$i] ?? 0)
                + ($comet_observations[$i] ?? 0)
                + ($double_observations[$i] ?? 0)
                + ($planets_observations[$i] ?? 0)
                + ($sun_observations[$i] ?? 0)
                + ($moon_observations[$i] ?? 0);
        }

        $chart = (new OriginalLineChart)
            ->setTitle(__('Number of observations per year: ') . $user->name)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData(__('Total'), $observations)
            ->addData(__('Deepsky'), $deepsky_observations)
            ->addData(__('Comets'), $comet_observations)
            ->setXAxisOption(new XAxisOption($x_axis))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);

        // Add optional additional series if we computed them
        if (!empty($double_observations)) {
            $chart->addData(__('Double stars'), $double_observations);
        }
        if (!empty($planets_observations)) {
            $chart->addData(__('Planets'), $planets_observations);
        }
        if (!empty($sun_observations)) {
            $chart->addData(__('Sun'), $sun_observations);
        }
        if (!empty($moon_observations)) {
            $chart->addData(__('Moon'), $moon_observations);
        }

        return $chart;
    }
}
