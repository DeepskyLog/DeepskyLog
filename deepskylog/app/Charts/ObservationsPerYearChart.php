<?php

namespace App\Charts;

use App\Models\Observation;
use App\Models\User;
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
        // Get the first observation of user.
        $query = Observation::where('observer_username', $user->username)
            ->where('legacy_source', 'deepsky')
            ->orderBy('observed_on', 'asc');

        $cometQuery = Observation::where('observer_username', $user->username)
            ->where('legacy_source', 'comet')
            ->orderBy('observed_on', 'asc');

        if ($query->count() == 0 && $cometQuery->count() == 0) {
            $deepsky_observations = [];
            $comet_observations = [];
            $observations = [];
            $x_axis = [];
        } else {
            if ($query->count() == 0) {
                $firstObservation = (int) substr((string) $cometQuery->first()->legacy_date_int, 0, 4);
            } elseif ($cometQuery->count() == 0) {
                $firstObservation = (int) substr((string) $query->first()->legacy_date_int, 0, 4);
            } else {
                $firstObservation = min(
                    (int) substr((string) $query->first()->legacy_date_int, 0, 4),
                    (int) substr((string) $cometQuery->first()->legacy_date_int, 0, 4)
                );
            }

            $deepsky_observations = [];
            $comet_observations = [];
            $observations = [];
            $x_axis = [];

            // Get the count of observations of the user for each year
            for ($year = $firstObservation; $year <= intval(date('Y')); $year++) {
                $deepsky = Observation::where('observer_username', $user->username)
                    ->where('legacy_source', 'deepsky')
                    ->whereYear('observed_on', $year)
                    ->count();
                $comets = Observation::where('observer_username', $user->username)
                    ->where('legacy_source', 'comet')
                    ->whereYear('observed_on', $year)
                    ->count();
                $deepsky_observations[] = $deepsky;
                $comet_observations[] = $comets;
                $observations[] = $deepsky + $comets;
                $x_axis[] = $year;
            }
        }

        return (new OriginalLineChart)
            ->setTitle(__('Number of observations per year: ') . $user->name)
            ->setSubtitle(__('Source: ') . config('app.old_url'))
            ->addData(__('Total'), $observations)
            ->addData(__('Deepsky'), $deepsky_observations)
            ->addData(__('Comets'), $comet_observations)
            ->setXAxisOption(new XAxisOption($x_axis))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }
}
