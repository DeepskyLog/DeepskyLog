<?php

namespace App\Charts;

use App\Models\User;
use App\Models\Observation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use marineusde\LarapexCharts\Charts\BarChart as OriginalBarChart;
use marineusde\LarapexCharts\Options\XAxisOption;

/**
 * Class ObservationsPerMonthChart
 *
 * This class is responsible for building a bar chart that represents the number of observations per month.
 * The chart includes deep sky, comet, double star, planet, sun and moon observations.
 */
class ObservationsPerMonthChart
{
    /**
     * Builds a bar chart of observations per month for a given user.
     *
     * This method counts the observations made by the user for each month and category, and returns a stacked
     * bar chart of them.
     *
     * The series come from the cached observer statistics ('observer_stats:{id}') when available. Otherwise the
     * 'observations' table is grouped by month of 'observed_on' for each category: deep sky and comets (by
     * 'target_type'), planets, sun, and moon and lunar features (by 'target_table'). Double stars are only filled
     * from the cache.
     *
     * Each series has 12 values (one per month), and the x-axis labels are the names of the months.
     *
     * @param  User  $user  The user for whom the chart is to be built.
     * @return OriginalBarChart The built bar chart.
     */
    public function build(User $user): OriginalBarChart
    {
        // Prefer cached precomputed series when available to avoid runtime DB work
        $stats = Cache::get('observer_stats:' . $user->id);
        if ($stats && isset($stats['perMonth'])) {
            $pm = $stats['perMonth'];
            $deepsky = array_map('intval', $pm['deepsky'] ?? array_fill(0, 12, 0));
            $comets = array_map('intval', $pm['comets'] ?? array_fill(0, 12, 0));
            $double = array_map('intval', $pm['double'] ?? array_fill(0, 12, 0));
            $planets = array_map('intval', $pm['planets'] ?? array_fill(0, 12, 0));
            $sun = array_map('intval', $pm['sun'] ?? array_fill(0, 12, 0));
            $moon = array_map('intval', $pm['moon'] ?? array_fill(0, 12, 0));

            $chart = (new OriginalBarChart)
                ->setTitle(__('Number of observations per month: ') . $user->name)
                ->setSubtitle(__('Source: ') . config('app.url'))
                ->setTheme('dark')
                ->setFontColor('#bbbbbb')
                ->setStacked(true)
                ->setToolbar(true)
                ->setXAxisOption(new XAxisOption([__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')]));

            $chart->addData(__('Deepsky'), $deepsky)
                ->addData(__('Comets'), $comets)
                ->addData(__('Double stars'), $double)
                ->addData(__('Planets'), $planets)
                ->addData(__('Sun'), $sun)
                ->addData(__('Moon'), $moon);

            return $chart;
        }

        // Aggregate counts per month using the Observation model so we can add extra categories
        $deepskyGrouped = Observation::selectRaw('MONTH(observed_on) as month, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_type', 'deepsky')
            ->groupByRaw('MONTH(observed_on)')
            ->get();

        $cometGrouped = Observation::selectRaw('MONTH(observed_on) as month, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_type', 'comet')
            ->groupByRaw('MONTH(observed_on)')
            ->get();

        $doubleGrouped = collect(); // skip expensive double-star EXISTS query; will be 0 on cold cache (job will warm it)

        $planetsGrouped = Observation::selectRaw('MONTH(observed_on) as month, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_table', 'planets')
            ->where('target_key', '!=', 'sun')
            ->groupByRaw('MONTH(observed_on)')
            ->get();

        $sunGrouped = Observation::selectRaw('MONTH(observed_on) as month, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->where('target_table', 'planets')
            ->where('target_key', 'sun')
            ->groupByRaw('MONTH(observed_on)')
            ->get();

        $moonGrouped = Observation::selectRaw('MONTH(observed_on) as month, count(*) as cnt')
            ->where('observer_username', $user->username)
            ->whereIn('target_table', ['moons', 'lunar_features'])
            ->groupByRaw('MONTH(observed_on)')
            ->get();

        $months = range(1, 12);
        $deepsky = array_fill(0, 12, 0);
        $comets = array_fill(0, 12, 0);
        $double = array_fill(0, 12, 0);
        $planets = array_fill(0, 12, 0);
        $sun = array_fill(0, 12, 0);
        $moon = array_fill(0, 12, 0);

        foreach ($deepskyGrouped as $r) {
            $deepsky[$r->month - 1] = (int) $r->cnt;
        }
        foreach ($cometGrouped as $r) {
            $comets[$r->month - 1] = (int) $r->cnt;
        }
        foreach ($doubleGrouped as $r) {
            $double[$r->month - 1] = (int) $r->cnt;
        }
        foreach ($planetsGrouped as $r) {
            $planets[$r->month - 1] = (int) $r->cnt;
        }
        foreach ($sunGrouped as $r) {
            $sun[$r->month - 1] = (int) $r->cnt;
        }
        foreach ($moonGrouped as $r) {
            $moon[$r->month - 1] = (int) $r->cnt;
        }

        $chart = (new OriginalBarChart)
            ->setTitle(__('Number of observations per month: ') . $user->name)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setStacked(true)
            ->setToolbar(true)
            ->setXAxisOption(new XAxisOption([__('January'), __('February'), __('March'), __('April'), __('May'), __('June'), __('July'), __('August'), __('September'), __('October'), __('November'), __('December')]));

        // Add series (keep Deepsky and Comets first to preserve previous ordering)
        $chart->addData(__('Deepsky'), $deepsky)
            ->addData(__('Comets'), $comets)
            ->addData(__('Double stars'), $double)
            ->addData(__('Planets'), $planets)
            ->addData(__('Sun'), $sun)
            ->addData(__('Moon'), $moon);

        return $chart;
    }
}
