<?php

namespace App\Charts;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\User;
use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;

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
        $query = ObservationsOld::where('observerid', $user->username)
            ->orderBy('date', 'asc');

        $cometQuery = CometObservationsOld::where('observerid', $user->username)
            ->orderBy('date', 'asc');

        if ($query->count() == 0 && $cometQuery->count() == 0) {
            $deepsky_observations = [];
            $comet_observations = [];
            $observations = [];
            $x_axis = [];
        } else {
            if ($query->count() == 0) {
                $firstObservation = $cometQuery->first()->date;
            } elseif ($cometQuery->count() == 0) {
                $firstObservation = $query->first()->date;
            } else {
                $firstObservation = min($query->first()->date, $cometQuery->first()->date);
            }

            // Drop last 4 characters of the integer to get the year.
            $firstObservation = intval(substr($firstObservation, 0, -4));

            $deepsky_observations = [];
            $comet_observations = [];
            $observations = [];
            $x_axis = [];

            // Get the count of observations of the user for each year
            for ($year = $firstObservation; $year <= intval(date('Y')); $year++) {
                $deepsky = ObservationsOld::where('observerid', $user->username)
                    ->where('date', '>=', $year.'0101')
                    ->where('date', '<=', $year.'1231')
                    ->count();
                $comets = CometObservationsOld::where('observerid', $user->username)
                    ->where('date', '>=', $year.'0101')
                    ->where('date', '<=', $year.'1231')
                    ->count();
                $deepsky_observations[] = $deepsky;
                $comet_observations[] = $comets;
                $observations[] = $deepsky + $comets;
                $x_axis[] = $year;
            }
        }

        return (new OriginalLineChart)
            ->setTitle(__('Number of observations per year: ').$user->name)
            ->setSubtitle(__('Source: ').config('app.old_url'))
            ->addData(__('Total'), $observations)
            ->addData(__('Deepsky'), $deepsky_observations)
            ->addData(__('Comets'), $comet_observations)
            ->setXAxis($x_axis)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }
}
