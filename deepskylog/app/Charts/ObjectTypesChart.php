<?php

namespace App\Charts;

use App\Models\CometObservationsOld;
use App\Models\ObservationsOld;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use marineusde\LarapexCharts\Charts\PieChart as OriginalPieChart;

class ObjectTypesChart
{
    public function build(User $user): OriginalPieChart
    {
        $observations[0] = CometObservationsOld::where('observerid', $user->username)->count();
        $names[0] = __('Comets');

        $deepskyobservations = ObservationsOld::where('observerid', $user->username)
            ->join('objects', 'observations.objectname', '=', 'objects.name')
            ->select('objects.type', DB::raw('count(*) as count'))
            ->groupBy('objects.type')
            ->get();

        foreach ($deepskyobservations as $key => $value) {
            $observations[$key + 1] = $value->count;
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

        if (array_search('Rest', $names)) {
            $rest = array_search('Rest', $names);
        } else {
            $names[] = __('Rest');
            $rest = array_search('Rest', $names);
            $observations[] = 0;
        }

        $total = ObservationsOld::where('observerid', $user->username)->count() + CometObservationsOld::where('observerid', $user->username)->count();

        for ($i = 0; $i < count($observations); $i++) {
            if ($observations[$i] < $total / 100) {
                $observations[$rest] += $observations[$i];
                $observations[$i] = 0;
            }
        }

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
