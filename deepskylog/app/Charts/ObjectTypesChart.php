<?php

namespace App\Charts;

use App\Models\User;
use App\Models\TargetType;
use App\Models\ObservationsOld;
use Illuminate\Support\Facades\DB;
use App\Models\CometObservationsOld;
use Illuminate\Database\Eloquent\Model;
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
            $value->type = __('' . TargetType::where('id', $value->type)->first()->type . '');
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
        return (new OriginalPieChart())
            ->setTitle(__('Object types seen: ').$user->name)
            ->setSubtitle(__('Source: ').config('app.old_url'))
            ->addData($observations)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true)
            ->setLabels($names);
    }
}
