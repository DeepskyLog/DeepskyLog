<?php

namespace App\Charts;

use App\Models\Observation;
use App\Models\TargetType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\PieChart as OriginalPieChart;

/**
 * Class ObjectTypesChart
 *
 * This class is responsible for building a pie chart that represents the number of observations per object type.
 */
class ObjectTypesChart
{
    /**
     * Builds a pie chart of observations per object type for a given user.
     *
     * Deep sky observations (target_table 'objects') are joined to the 'objects' table on the normalized name and
     * grouped by object type. Comets, planets, the sun, and the moon with its lunar features each get one slice.
     * Slices smaller than 1% of the total are grouped into 'Rest'.
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
            ->where('target_type', 'comet')
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
            ->where('target_key', '!=', 'sun')
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
            ->where('target_key', 'sun')
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
            ->setSubtitle(__('Source: ') . config('app.url'))
            ->addData($keep)
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true)
            ->setLabels($keepNames);
    }
}
