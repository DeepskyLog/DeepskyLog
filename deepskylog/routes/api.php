<?php

use App\Http\Controllers\EyepieceController;
use App\Http\Controllers\InstrumentController;
use App\Models\Atlas;
use App\Models\InstrumentsOld;
use App\Models\LocationsOld;
use App\Models\User;
use Illuminate\Http\Request;
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
            'name' => Countries::getOne(auth()->user()->country, $request->lang),
        ];
    }
    foreach (Countries::getList($request->lang) as $code => $name) {
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
        'Enter your own copyright text',
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
            'name' => $text,
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
        'name' => 'No standard location (Not recommended)',
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
        'name' => 'No standard instrument (Not recommended)',
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

Route::get('ui_languages.index', function (Request $request) {
    $allLanguages = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allLanguages[] = [
            'id' => auth()->user()->language,
            'name' => Languages::lookup([auth()->user()->language], 'mixed')->values()[0],
        ];
    }

    // Get the languages
    foreach (config('app.available_locales') as $language => $key) {
        $language = Languages::lookup([$key], $key)[$key];
        if ($request->search == '' || Str::contains(Str::lower($language), Str::lower($request->search))) {
            $allLanguages[] = [
                'id' => $key,
                'name' => $language,
            ];
        }
    }

    return $allLanguages;
})->name('ui_languages.index');

// Make a list of all possible languages for the observations in DeepskyLog.
Route::get('observation_languages.index', function (Request $request) {
    $allLanguages = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allLanguages[] = [
            'id' => auth()->user()->observationlanguage,
            'name' => Languages::lookup([auth()->user()->observationlanguage], 'mixed')->values()[0],
        ];
    }

    // Get the languages
    foreach (Languages::lookup(['bg', 'hr', 'cs', 'da', 'nl', 'en', 'eo', 'fi', 'fr', 'de', 'el', 'is', 'it', 'no', 'pl', 'pt', 'es', 'sv'], 'mixed') as $key => $language) {
        if ($request->search == '' || Str::contains(Str::lower($language), Str::lower($request->search))) {
            $allLanguages[] = [
                'id' => $key,
                'name' => $language,
            ];
        }
    }

    return $allLanguages;
})->name('observation_languages.index');

// Make a list of all users to add to a team.
Route::get('addUserToTeam.index', function (Request $request) {
    $allUsers = [];

    // Get the languages
    foreach (User::all() as $user) {
        if ($request->search == '' || Str::contains(Str::lower($user->name), Str::lower($request->search)) || Str::contains(Str::lower($user->email), Str::lower($request->search))) {
            $allUsers[] = [
                'id' => $user->email,
                'name' => $user->name.' ('.$user->email.')',
            ];
        }
    }

    return $allUsers;
})->name('addUserToTeam.index');

Route::get('/instruments/{userid}', [InstrumentController::class, 'show_from_user']);
Route::get('/eyepieces/{userid}', [EyepieceController::class, 'show_from_user']);
