<?php

namespace App\Http\Controllers;

use App\Services\ObserverCountries;
use App\Services\PopularObjectsRanking;
use Illuminate\Http\Request;

class PopularObjectController extends Controller
{
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'observations');
        if (!in_array($mode, PopularObjectsRanking::MODES, true)) {
            $mode = 'observations';
        }

        $countryOptions = ObserverCountries::options();
        $country = strtoupper(trim((string) $request->query('country', '')));

        return view('observations.popular-objects', [
            'mode' => $mode,
            'countryOptions' => $countryOptions,
            'country' => array_key_exists($country, $countryOptions) ? $country : null,
        ]);
    }
}
