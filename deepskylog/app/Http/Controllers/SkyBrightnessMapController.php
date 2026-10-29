<?php

namespace App\Http\Controllers;

use App\Services\SkyBrightnessMap;
use Illuminate\Http\Request;

/**
 * The community sky-brightness map (App\Services\SkyBrightnessMap): the
 * SQM and NELM of the observations per cell, never per location.
 */
class SkyBrightnessMapController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period');
        $period = SkyBrightnessMap::isPeriod($period) ? $period : 'all';
        $quantity = in_array($request->query('quantity'), ['sqm', 'nelm'], true) ? $request->query('quantity') : 'sky';

        // The map starts at the viewer's standard location, which they know already
        $home = $request->user()?->standardLocation;

        return view('sky-brightness-map.index', [
            'period' => $period,
            'quantity' => $quantity,
            'cells' => SkyBrightnessMap::cells($period),
            'center' => $home && $home->latitude !== null && $home->longitude !== null
                ? [(float) $home->latitude, (float) $home->longitude]
                : null,
        ]);
    }
}
