<?php

namespace App\Charts;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\PieChart as OriginalPieChart;

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
class ObjectTypesChart
{
    /**
     * Builds a pie chart of observations per object type for a given user.
     *
     * This method retrieves the count of comet observations and deep sky observations made by the user,
     * and stores them in an array. It then creates a pie chart with these data, sets the title, subtitle, colors,
     * and other properties of the chart, and returns it.
     *
     * The method uses raw SQL queries to retrieve the count of observations from the 'cometobservations' and 'observations' tables.
     * The queries join the 'objects' table on the 'objectname' column, select the 'type' column from the 'objects' table and the count of observations,
     * and group them by 'type'.
     *
     * The method then initializes two arrays, and populates them with the object types and the count of observations for each type.
     *
     * Finally, the method creates a new OriginalPieChart object, sets its properties, and adds the count of observations as data and the object types as labels.
     *
     * @param  User  $user  The user for whom the chart is to be built.
     * @return OriginalPieChart The built pie chart.
     */
    public function build(User $user): OriginalPieChart
    {
        $observations[0] = CometObservationsOld::where('observerid', $user->username)->count();
        $names[0] = __('Comets');

        $deepskyobservations = ObservationsOld::where('observerid', $user->username)
            ->join('objects', 'observations.objectname', '=', 'objects.name')
            ->select('objects.type', DB::raw('count(*) as count'))
            ->groupBy('objects.type')
            ->get();

        // Loop through the deep sky observations and add the count and type to the respective arrays
        foreach ($deepskyobservations as $key => $value) {
            $observations[$key + 1] = $value->count;
            // Translate the object type code to a human-readable string
            $value->type = match ($value->type) {
                'CLANB' => __('Cluster with nebulosity'),
                'PLNNB' => __('Planetary nebula'),
                'OPNCL' => __('Open cluster'),
                'SNREM' => __('Supernova remnant'),
                'GALXY' => __('Galaxy'),
                'EMINB' => __('Emission nebula'),
                'GLOCL' => __('Globular cluster'),
                'NONEX' => __('Nonexistent'),
                'WRNEB' => __('Wolf-Rayet nebula'),
                'DRKNB' => __('Dark nebula'),
                'HII' => __('H-II region'),
                'RNHII' => __('Reflection nebula and H-II'),
                'ENRNN' => __('Emission and reflection nebula'),
                'GALCL' => __('Galaxy cluster'),
                'REFNB' => __('Reflection nebula'),
                'ASTER' => __('Asterism'),
                'DS' => __('Double star'),
                'GACAN' => __('Cluster with nebulosity in galaxy'),
                'QUASR' => __('Quasar'),
                'BRTNB' => __('Bright nebula'),
                'ENSTR' => __('Emission nebula around a star'),
                'GXADN' => __('Diffuse nebula in galaxy'),
                'GXAGC' => __('Globular cluster in galaxy'),
                'LMCCN' => __('Cluster with nebulosity in LMC'),
                'LMCDN' => __('Diffuse nebula in LMC'),
                'LMCGC' => __('Globular cluster in LMC'),
                'LMCOC' => __('Open cluster in LMC'),
                'SMCCN' => __('Cluster with nebulosity in SMC'),
                'SMCDN' => __('Diffuse nebula in SMC'),
                'SMCGC' => __('Globular cluster in SMC'),
                'SMCOC' => __('Open cluster in SMC'),
                'SNOVA' => __('Supernova'),
                'STNEB' => __('Nebula around star'),
                'AA1STAR' => __('Star'),
                'AA3STAR' => __('3 stars'),
                'AA4STAR' => __('4 stars'),
                'AA8STAR' => __('8 stars'),
                default => __('Rest'),
            };
            $names[$key + 1] = $value->type;
        }

        // If there are any observations that don't fit into the predefined types, group them under 'Rest'
        if (array_search('Rest', $names)) {
            $rest = array_search('Rest', $names);
        } else {
            $names[] = __('Rest');
            $rest = array_search('Rest', $names);
            $observations[] = 0;
        }

        // Calculate the total number of observations
        $total = ObservationsOld::where('observerid', $user->username)->count() + CometObservationsOld::where('observerid', $user->username)->count();

        // If the count of observations for a type is less than 1% of the total, group it under 'Rest'
        for ($i = 0; $i < count($observations); $i++) {
            if ($observations[$i] < $total / 100) {
                $observations[$rest] += $observations[$i];
                $observations[$i] = 0;
            }
        }

        // Create the pie chart and return it
        return (new OriginalPieChart)
            ->setTitle(__('Object types seen: '.$user->name))
            ->setSubtitle(__('Source: ').config('app.old_url'))
            ->addData($observations)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true)
            ->setLabels($names);
    }
}
