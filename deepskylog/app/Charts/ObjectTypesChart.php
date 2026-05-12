<?php

namespace App\Charts;

use App\Models\Observation;
use App\Models\TargetType;
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
        $userWhere = function ($q) use ($user) {
            $q->where('observations.observer_user_id', $user->id)
                ->orWhere('observations.observer_username', $user->username);
        };

        // Deepsky/double-star objects grouped by type — use normalized index join for accuracy
        $deepskyRows = DB::table('observations')
            ->join('objects', 'observations.target_name_normalized', '=', 'objects.name_normalized')
            ->where($userWhere)
            ->where('observations.target_table', 'objects')
            ->select('objects.type', DB::raw('count(*) as count'))
            ->groupBy('objects.type')
            ->get();

        // Preload all target type labels to avoid N+1
        $targetTypes = TargetType::all()->keyBy('id');

        $observations = [];
        $names = [];

        foreach ($deepskyRows as $row) {
            $label = $targetTypes->get($row->type)?->type ?? $row->type;
            $observations[] = (int) $row->count;
            $names[] = __($label);
        }

        // Comets
        $cometCount = (int) DB::table('observations')
            ->where('legacy_source', 'comet')
            ->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)
                    ->orWhere('observer_username', $user->username);
            })->count();
        if ($cometCount > 0) {
            $observations[] = $cometCount;
            $names[] = __('Comets');
        }

        // Planets (excluding sun)
        $planetsCount = (int) DB::table('observations')
            ->where('target_table', 'planets')
            ->whereRaw('LOWER(target_key) != ?', ['sun'])
            ->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)
                    ->orWhere('observer_username', $user->username);
            })->count();
        if ($planetsCount > 0) {
            $observations[] = $planetsCount;
            $names[] = __('Planets');
        }

        // Sun
        $sunCount = (int) DB::table('observations')
            ->where('target_table', 'planets')
            ->whereRaw('LOWER(target_key) = ?', ['sun'])
            ->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)
                    ->orWhere('observer_username', $user->username);
            })->count();
        if ($sunCount > 0) {
            $observations[] = $sunCount;
            $names[] = __('Sun');
        }

        // Moon and lunar features
        $moonCount = (int) DB::table('observations')
            ->whereIn('target_table', ['moons', 'lunar_features'])
            ->where(function ($q) use ($user) {
                $q->where('observer_user_id', $user->id)
                    ->orWhere('observer_username', $user->username);
            })->count();
        if ($moonCount > 0) {
            $observations[] = $moonCount;
            $names[] = __('Moon');
        }

        // Group small slices (<1% of total) into 'Rest'
        $total = array_sum($observations);
        $restCount = 0;
        $keep = [];
        $keepNames = [];
        for ($i = 0; $i < count($observations); $i++) {
            if ($total > 0 && $observations[$i] < $total / 100) {
                $restCount += $observations[$i];
            } else {
                $keep[] = $observations[$i];
                $keepNames[] = $names[$i];
            }
        }
        if ($restCount > 0) {
            $keep[] = $restCount;
            $keepNames[] = __('Rest');
        }

        return (new OriginalPieChart)
            ->setTitle(__('Object types seen: ') . $user->name)
            ->setSubtitle(__('Source: ') . config('app.old_url'))
            ->addData($keep)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true)
            ->setLabels($keepNames);
    }
}
