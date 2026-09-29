<?php

namespace App\Charts;

use App\Models\Observation;
use App\Models\Country;
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
     * This method counts the observations (of all target types) made by the user for each country. The query joins
     * the 'locations' table on the 'location_id' column of the 'observations' table and groups by the 'country' column.
     * Country names are localized, and counts of names that end up the same are merged.
     *
     * Finally, the method creates a new OriginalPieChart object, sets its properties, and adds the count of observations as data and the countries as labels.
     *
     * @param  User  $user  The user for whom the chart is to be built.
     * @return OriginalPieChart The built pie chart.
     */
    public function build(User $user): OriginalPieChart
    {
        $deepsky = Observation::where('observer_username', $user->username)->join('locations', 'observations.location_id', '=', 'locations.id')->select('locations.country', DB::raw('count(*) as count'))->groupBy('locations.country')->get();

        $countries = [];
        $values = [];

        // Build country labels and values.
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

        return (new OriginalPieChart)
            ->setTitle(__('Observations per country: ') . $user->name)
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData($values)
            ->setLabels($countries)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);

    }
}
