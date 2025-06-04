<?php

use App\Http\Controllers\EyepieceController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\LensController;
use App\Models\Atlas;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\FilterColor;
use App\Models\FilterMake;
use App\Models\FilterType;
use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\InstrumentType;
use App\Models\LensMake;
use App\Models\LocationsOld;
use App\Models\MountType;
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

Route::get('locations.api', function (Request $request) {
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
})->name('locations.api');

Route::get('instrument.api', function (Request $request) {
    $allInstruments = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allInstruments[] = [
            'id' => auth()->user()->stdtelescope,
            'name' => Instrument::where('id', auth()->user()->stdtelescope)->first()->fullName(),
        ];
    }

    // Get the instruments, but they should be active
    $instruments = Instrument::where('observer', auth()->user()->username)->where('active', 1)->get();

    foreach ($instruments as $instrument) {
        if ($request->search == '' || Str::contains(Str::lower($instrument->fullName()), Str::lower($request->search))) {
            $allInstruments[] = [
                'id' => $instrument->id,
                'name' => $instrument->fullName(),
            ];
        }
    }
    $allInstruments[] = [
        'id' => 0,
        'name' => 'No standard instrument (Not recommended)',
    ];

    return $allInstruments;
})->name('instrument.api');

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

Route::get('instrument_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allMakes[] = [
            'id' => InstrumentMake::where('id', $request->selected)->first()->id,
            'name' => InstrumentMake::where('id', $request->selected)->first()->name,
        ];
    }
    $makes = InstrumentMake::get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('instrument_makes.api');

Route::get('instrument_types.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allTypes[] = [
            'id' => InstrumentType::where('id', $request->selected)->first()->id,
            'name' => InstrumentType::where('id', $request->selected)->first()->name,
        ];
    }
    $types = InstrumentType::get();
    foreach ($types as $type) {
        if ($request->search == '' || Str::contains(Str::lower($type->name), Str::lower($request->search))) {
            $allTypes[] = [
                'id' => $type->id,
                'name' => $type->name,
            ];
        }
    }

    return $allTypes;
})->name('instrument_types.api');

Route::get('mount_types.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allTypes[] = [
            'id' => MountType::where('id', $request->selected)->first()->id,
            'name' => MountType::where('id', $request->selected)->first()->name,
        ];

    }
    $types = MountType::get();
    foreach ($types as $type) {
        if ($request->search == '' || Str::contains(Str::lower($type->name), Str::lower($request->search))) {
            $allTypes[] = [
                'id' => $type->id,
                'name' => $type->name,
            ];
        }
    }

    return $allTypes;
})->name('mount_types.api');

Route::get('eyepiece_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allMakes[] = [
            'id' => EyepieceMake::where('id', $request->selected)->first()->id,
            'name' => EyepieceMake::where('id', $request->selected)->first()->name,
        ];
    }
    $makes = EyepieceMake::get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('eyepiece_makes.api');

Route::get('eyepiece_type.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allTypes[] = [
            'id' => EyepieceType::where('id', $request->selected)->first()->id,
            'name' => EyepieceType::where('id', $request->selected)->first()->name,
        ];
    }
    if ($request->exists('make')) {
        $allTypes[] = [
            'id' => 0,
            'name' => '',
        ];
        $types = EyepieceType::where('eyepiece_makes_id', $request->make)->get();
        foreach ($types as $type) {
            if ($request->search == '' || Str::contains(Str::lower($type->name), Str::lower($request->search))) {
                $allTypes[] = [
                    'id' => $type->id,
                    'name' => $type->name,
                ];
            }
        }
    } else {
        $types = EyepieceType::get();
        $allTypes[] = [
            'id' => 0,
            'name' => '',
        ];
    }

    return $allTypes;
})->name('eyepiece_types.api');

Route::get('lens_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allMakes[] = [
            'id' => LensMake::where('id', $request->selected)->first()->id,
            'name' => LensMake::where('id', $request->selected)->first()->name,
        ];
    }
    $makes = LensMake::get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('lens_makes.api');

Route::get('filter_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allMakes[] = [
            'id' => FilterMake::where('id', $request->selected)->first()->id,
            'name' => FilterMake::where('id', $request->selected)->first()->name,
        ];
    }
    $makes = FilterMake::get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('filter_makes.api');

Route::get('filter_colors.api', function (Request $request) {
    $allColors = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allColors[] = [
            'id' => FilterColor::where('id', $request->selected)->first()->id,
            'name' => FilterColor::where('id', $request->selected)->first()->name,
        ];
    }
    $colors = FilterColor::get();
    foreach ($colors as $color) {
        if ($request->search == '' || Str::contains(Str::lower($color->name), Str::lower($request->search))) {
            $allColors[] = [
                'id' => $color->id,
                'name' => $color->name,
            ];
        }
    }

    return $allColors;
})->name('filter_colors.api');

Route::get('filter_types.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allTypes[] = [
            'id' => FilterType::where('id', $request->selected)->first()->id,
            'name' => FilterType::where('id', $request->selected)->first()->name,
        ];
    }
    $types = FilterType::get();
    foreach ($types as $type) {
        if ($request->search == '' || Str::contains(Str::lower($type->name), Str::lower($request->search))) {
            $allTypes[] = [
                'id' => $type->id,
                'name' => $type->name,
            ];
        }
    }

    return $allTypes;
})->name('filter_types.api');

Route::get('/instrument/{userid}', [InstrumentController::class, 'show_from_user']);
Route::get('/eyepieces/{userid}', [EyepieceController::class, 'show_from_user']);
Route::get('/lenses/{userid}', [LensController::class, 'show_from_user']);
