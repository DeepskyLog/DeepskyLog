<?php

namespace App\Http\Controllers;

use App\Services\ObserverCountries;
use Illuminate\Http\Request;

class PopularObservationController extends Controller
{
    /**
     * The list itself (with its search) is the PopularObservationsTable
     * Livewire component.
     */
    public function index(Request $request)
    {
        $countryOptions = ObserverCountries::options();
        $country = strtoupper(trim((string) $request->query('country', '')));

        return view('observations.popular', [
            'countryOptions' => $countryOptions,
            'country' => array_key_exists($country, $countryOptions) ? $country : null,
        ]);
    }
}
