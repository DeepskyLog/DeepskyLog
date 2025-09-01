<?php

namespace App\Charts;

use App\Models\CometObservationsOld;
use App\Models\Country;
use App\Models\ObservationsOld;
use App\Models\User;
use Countries;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\PieChart as OriginalPieChart;

/**
 * Class CountriesChart
 *
 * This class is responsible for building a pie chart that represents the number of observations per country.
 */
class CountriesChart
{
    /**
     * Builds a pie chart of observations per country for a given user.
     *
     * This method retrieves the count of comet observations and deep sky observations made by the user for each country,
     * and stores them in two separate arrays. It then creates a pie chart with these data, sets the title, subtitle, colors,
     * and other properties of the chart, and returns it.
     *
     * The method uses raw SQL queries to retrieve the count of observations from the 'cometobservations' and 'observations' tables.
     * The queries join the 'locations' table on the 'locationid' column, select the 'country' column from the 'locations' table and the count of observations,
     * and group them by 'country'.
     *
     * The method then initializes two arrays, and populates them with the countries and the count of observations for each country.
     *
     * Finally, the method creates a new OriginalPieChart object, sets its properties, and adds the count of observations as data and the countries as labels.
     *
     * @param  User  $user  The user for whom the chart is to be built.
     * @return OriginalPieChart The built pie chart.
     */
    public function build(User $user): OriginalPieChart
    {
        $deepsky = ObservationsOld::where('observerid', $user->username)->join('locations', 'observations.locationid', '=', 'locations.id')->select('locations.country', DB::raw('count(*) as count'))->groupBy('locations.country')->get();

        $comets = CometObservationsOld::where('observerid', $user->username)->join('locations', 'cometobservations.locationid', '=', 'locations.id')->select('locations.country', DB::raw('count(*) as count'))->groupBy('locations.country')->get();

        $countries = [];
        $values = [];

        // Build country labels and values from deep-sky observations.
        foreach ($deepsky as $item) {
            // Try to find a Country model for this location country value.
            $countryModel = Country::where('country', $item->country)->first();

            // Prefer localized name from Countries::getOne when we have a code.
            if ($countryModel && $countryModel->code) {
                $countryName = Countries::getOne($countryModel->code, app()->getLocale()) ?? $countryModel->country;
            } else {
                // Fallback to the raw country text from the locations table if no Country model is found.
                $countryName = $item->country;
            }

            // Skip empty country names.
            if (empty($countryName)) {
                continue;
            }

            // Merge counts when the same country appears multiple times.
            if (in_array($countryName, $countries, true)) {
                $values[array_search($countryName, $countries, true)] += $item->count;
            } else {
                $countries[] = $countryName;
                $values[] = $item->count;
            }
        }

        // Add comet observations, merging into the same arrays and handling missing Country models.
        foreach ($comets as $comet) {
            $countryModel = Country::where('country', $comet->country)->first();

            if ($countryModel && $countryModel->code) {
                $countryName = Countries::getOne($countryModel->code, app()->getLocale()) ?? $countryModel->country;
            } else {
                $countryName = $comet->country;
            }

            if (empty($countryName)) {
                continue;
            }

            if (in_array($countryName, $countries, true)) {
                $values[array_search($countryName, $countries, true)] += $comet->count;
            } else {
                $countries[] = $countryName;
                $values[] = $comet->count;
            }
        }

        return (new OriginalPieChart)
            ->setTitle(__('Observations per country: ').$user->name)
            ->setSubtitle(__('Source: ').config('app.old_url'))
            ->addData($values)
            ->setLabels($countries)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);

    }
}
