<?php

use App\Models\InstrumentsOld;
use App\Models\LocationsOld;
use App\Models\Atlas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('countries.index', function (Request $request) {
    $allCountries = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allCountries[] = [
            'id' => auth()->user()->country,
            'name' => \Countries::getOne(auth()->user()->country, 'en'),
        ];
    }
    foreach (\Countries::getList('en') as $code => $name) {
        if ($request->search == '' || Str::contains(Str::lower($name), Str::lower($request->search))) {
            $allCountries[] = [
                'id' => $code,
                'name' => $name,
            ];
        }
    }

    return $allCountries;
})->name('countries.index');

Route::get('licenses.index', function (Request $request) {
    $licenses = [
        'Attribution CC BY',
        'Attribution-ShareAlike CC BY-SA',
        'Attribution-NoDerivs CC BY-ND',
        'Attribution-NonCommercial CC BY-NC',
        'Attribution-NonCommercial-ShareAlike CC BY-NC-SA',
        'Attribution-NonCommercial-NoDerivs CC BY-NC-ND',
        'No license (Not recommended)',
        'Enter your own copyright text'
    ];

    $allLicenses = [];
    // Show the selected option
    if ($request->exists('selected')) {
        if (in_array(auth()->user()->copyrightSelection, $licenses)) {
            $text = auth()->user()->copyrightSelection;
        } elseif (auth()->user()->copyrightSelection === 'No license (Not recommended)') {
            $text = 'No license (Not recommended)';
        } else {
            $text = 'Enter your own copyright text';
        }
        $allLicenses[] = [
            'name' => $text
        ];
    }
    foreach ($licenses as $text) {
        if ($request->search == '' || Str::contains(Str::lower($text), Str::lower($request->search))) {
            $allLicenses[] = [
                'name' => $text,
            ];
        }
    }

    return $allLicenses;
})->name('licenses.index');

Route::get('locations.index', function (Request $request) {
    $allLocations = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allLocations[] = [
            'id' => auth()->user()->stdlocation,
            'name' => LocationsOld::where('id', auth()->user()->stdlocation)->first()->name,
        ];
    }

    // Get the location, but they should be active
    $locations = LocationsOld::where('observer', auth()->user()->username)->where('locationactive', 1)->get();

    foreach ($locations as $location) {
        if ($request->search == '' || Str::contains(Str::lower($location->name), Str::lower($request->search))) {
            $allLocations[] = [
                'id' => $location->id,
                'name' => $location->name,
            ];
        }
    }
    $allLocations[] = [
        'id' => 0,
        'name' => 'No standard location (Not recommended)'
    ];

    return $allLocations;
})->name('locations.index');

Route::get('instruments.index', function (Request $request) {
    $allInstruments = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allInstruments[] = [
            'id' => auth()->user()->stdtelescope,
            'name' => InstrumentsOld::where('id', auth()->user()->stdtelescope)->first()->name,
        ];
    }

    // Get the instrument, but they should be active
    $instruments = InstrumentsOld::where('observer', auth()->user()->username)->where('instrumentactive', 1)->get();

    foreach ($instruments as $instrument) {
        if ($request->search == '' || Str::contains(Str::lower($instrument->name), Str::lower($request->search))) {
            $allInstruments[] = [
                'id' => $instrument->id,
                'name' => $instrument->name,
            ];
        }
    }
    $allInstruments[] = [
        'id' => 0,
        'name' => 'No standard instrument (Not recommended)'
    ];

    return $allInstruments;
})->name('instruments.index');

Route::get('atlas.index', function (Request $request) {
    $allAtlases = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allAtlases[] = [
            'id' => auth()->user()->standardAtlasCode,
            'name' => Atlas::where('code', auth()->user()->standardAtlasCode)->first()->name,
        ];
    }

    // Get the atlases
    foreach (Atlas::all() as $atlas) {
        if ($request->search == '' || Str::contains(Str::lower($atlas->name), Str::lower($request->search))) {
            $allAtlases[] = [
                'id' => $atlas->code,
                'name' => $atlas->name,
            ];
        }
    }

    return $allAtlases;
})->name('atlas.index');
