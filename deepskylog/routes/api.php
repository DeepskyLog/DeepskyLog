<?php

use App\Http\Controllers\EyepieceController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\LensController;
use App\Models\Atlas;
use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\Filter;
use App\Models\FilterColor;
use App\Models\FilterMake;
use App\Models\FilterType;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\InstrumentMake;
use App\Models\InstrumentType;
use App\Models\Lens;
use App\Models\LensMake;
use App\Models\Location;
use App\Models\MountType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
            'name' => Location::where('id', auth()->user()->stdlocation)->first()->name,
            'description' => Location::where('id', auth()->user()->stdlocation)->first()->description ?? '',
        ];
    }

    // Get the location, but they should be active
    $locations = Location::where('observer', auth()->user()->username)->where('locationactive', 1)->get();

    foreach ($locations as $location) {
        if ($request->search == '' || Str::contains(Str::lower($location->name), Str::lower($request->search))) {
            $allLocations[] = [
                'id' => $location->id,
                'name' => $location->name,
                'description' => $location->description ?? '',
            ];
        }
    }
    $allLocations[] = [
        'id' => 0,
        'name' => 'No standard location (Not recommended)',
    ];

    return $allLocations;
})->name('locations.api');


// Location select helper that correctly returns any selected locations (supports arrays)
Route::get('location.select.api', function (Request $request) {

    $allLocations = [];

    // Support an alternative comma-separated parameter `selected_ids`.
    // Some callers (Blade attributes/HTML) may end up with HTML-escaped ampersands
    // which break array-style querystrings like selected[0]=..&selected[1]=..
    // Accept `selected_ids=1,2,3` and normalize it to `selected` so the
    // existing code below can continue to work unchanged.
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $ids = array_filter(array_map('trim', explode(',', $request->selected_ids)));
        if (!empty($ids)) {
            $request->merge(['selected' => $ids]);
        }
    }

    // Show the selected options (supports single value or array)
    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && ($request->search == '' || $request->search === null)) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $loc = Location::where('id', $sel)->first();
            if ($loc) {
                $allLocations[] = [
                    'id' => $loc->id,
                    'name' => $loc->name,
                    'description' => $loc->description,
                ];
            }
        }

        return $allLocations;
    }

    // Otherwise return selected first + the full (or filtered) list
    if ($request->exists('selected')) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $loc = Location::where('id', $sel)->first();
            if ($loc) {
                $allLocations[] = [
                    'id' => $loc->id,
                    'name' => $loc->name,
                    'description' => $loc->description,
                ];
            }
        }
    }

    // Get the locations for the authenticated user (only active ones) and sort alphabetically
    $locations = Location::where('observer', auth()->user()->username)->where('active', 1)->get();
    $locations = $locations->sortBy(function ($l) {
        return Str::lower($l->name);
    });

    foreach ($locations as $location) {
        if ($request->search == '' || Str::contains(Str::lower($location->fullName()), Str::lower($request->search))) {
            $allLocations[] = [
                'id' => $location->id,
                'name' => $location->name,
                'description' => $location->description,
            ];
        }
    }

    // Remove duplicates while preserving order
    $seen = [];
    $uniqueLocations = [];
    foreach ($allLocations as $location) {
        if (!isset($seen[$location['id']])) {
            $seen[$location['id']] = true;
            $uniqueLocations[] = $location;
        }
    }

    return $uniqueLocations;
})->name('location.select.api');

Route::get('instrument.api', function (Request $request) {
    $allInstruments = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $allInstruments[] = [
            'id' => auth()->user()->stdtelescope,
            'name' => Instrument::where('id', auth()->user()->stdtelescope)->first()->fullName(),
            'description' => Instrument::where('id', auth()->user()->stdtelescope)->first()->description,
        ];
    }

    // Get the instruments, but they should be active
    $instruments = Instrument::where('observer', auth()->user()->username)->where('active', 1)->get();

    foreach ($instruments as $instrument) {
        if ($request->search == '' || Str::contains(Str::lower($instrument->fullName()), Str::lower($request->search))) {
            $allInstruments[] = [
                'id' => $instrument->id,
                'name' => $instrument->fullName(),
                'description' => $instrument->description,
            ];
        }
    }
    $allInstruments[] = [
        'id' => 0,
        'name' => 'No standard instrument (Not recommended)',
    ];

    return $allInstruments;
})->name('instrument.api');

Route::get('instrumentset.api', function (Request $request) {
    $allSets = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $selectedSet = auth()->user()->standardInstrumentSet;
        if ($selectedSet) {
            $allSets[] = [
                'id' => $selectedSet->id,
                'name' => $selectedSet->name,
                'description' => $selectedSet->description ?? '',
            ];
        }
    }

    // Get the instrument sets, only active ones
    $sets = InstrumentSet::where('user_id', auth()->user()->id)->where('active', 1)->get();

    foreach ($sets as $set) {
        if ($request->search == '' || Str::contains(Str::lower($set->name), Str::lower($request->search))) {
            $allSets[] = [
                'id' => $set->id,
                'name' => $set->name,
                'description' => $set->description ?? '',
            ];
        }
    }
    $allSets[] = [
        'id' => 0,
        'name' => 'No standard instrument set (Not recommended)',
    ];

    return $allSets;
})->name('instrumentset.api');

// InstrumentSet select helper that correctly returns any selected instrument sets (supports arrays)
Route::get('instrumentset.select.api', function (Request $request) {

    $allSets = [];

    // Support an alternative comma-separated parameter `selected_ids`.
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $ids = array_filter(array_map('trim', explode(',', $request->selected_ids)));
        if (!empty($ids)) {
            $request->merge(['selected' => $ids]);
        }
    }

    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && ($request->search == '' || $request->search === null)) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            // Prefer the user's relation when possible
            if ($sel == auth()->user()->stdinstrumentset) {
                $s = auth()->user()->standardInstrumentSet;
            } else {
                $s = InstrumentSet::where('id', $sel)->first();
            }
            if ($s) {
                $allSets[] = [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description ?? '',
                ];
            }
        }

        return $allSets;
    }

    // Otherwise return selected first + the full (or filtered) list
    if ($request->exists('selected')) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $s = InstrumentSet::where('id', $sel)->first();
            if ($s) {
                $allSets[] = [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description ?? '',
                ];
            }
        }
    }

    // Get the instrument sets for the authenticated user (only active ones)
    $sets = InstrumentSet::where('user_id', auth()->user()->id)->where('active', 1)->get();

    foreach ($sets as $set) {
        if ($request->search == '' || Str::contains(Str::lower($set->name), Str::lower($request->search))) {
            $allSets[] = [
                'id' => $set->id,
                'name' => $set->name,
                'description' => $set->description ?? '',
            ];
        }
    }

    // Remove duplicates while preserving order
    $seen = [];
    $uniqueSets = [];
    foreach ($allSets as $set) {
        if (!isset($seen[$set['id']])) {
            $seen[$set['id']] = true;
            $uniqueSets[] = $set;
        }
    }

    return $uniqueSets;
})->name('instrumentset.select.api');

// Instrument select helper that correctly returns any selected instruments (supports arrays)
Route::get('instrument.select.api', function (Request $request) {

    $allInstruments = [];

    // Support an alternative comma-separated parameter `selected_ids`.
    // Some callers (Blade attributes/HTML) may end up with HTML-escaped ampersands
    // which break array-style querystrings like selected[0]=..&selected[1]=..
    // Accept `selected_ids=1,2,3` and normalize it to `selected` so the
    // existing code below can continue to work unchanged.
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $ids = array_filter(array_map('trim', explode(',', $request->selected_ids)));
        if (!empty($ids)) {
            $request->merge(['selected' => $ids]);
        }
    }

    // Show the selected options (supports single value or array)
    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && ($request->search == '' || $request->search === null)) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $inst = Instrument::where('id', $sel)->first();
            if ($inst) {
                $allInstruments[] = [
                    'id' => $inst->id,
                    'name' => $inst->fullName(),
                    'description' => $inst->description,
                ];
            }
        }

        return $allInstruments;
    }

    // Otherwise return selected first + the full (or filtered) list
    if ($request->exists('selected')) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $inst = Instrument::where('id', $sel)->first();
            if ($inst) {
                $allInstruments[] = [
                    'id' => $inst->id,
                    'name' => $inst->fullName(),
                    'description' => $inst->description,
                ];
            }
        }
    }

    // Get the instruments for the authenticated user (only active ones) and sort alphabetically
    $instruments = Instrument::where('observer', auth()->user()->username)->where('active', 1)->get();
    $instruments = $instruments->sortBy(function ($i) {
        return Str::lower($i->fullName());
    });

    foreach ($instruments as $instrument) {
        if ($request->search == '' || Str::contains(Str::lower($instrument->fullName()), Str::lower($request->search))) {
            $allInstruments[] = [
                'id' => $instrument->id,
                'name' => $instrument->fullName(),
                'description' => $instrument->description,
            ];
        }
    }

    // Remove duplicates while preserving order
    $seen = [];
    $uniqueInstruments = [];
    foreach ($allInstruments as $instrument) {
        if (!isset($seen[$instrument['id']])) {
            $seen[$instrument['id']] = true;
            $uniqueInstruments[] = $instrument;
        }
    }

    return $uniqueInstruments;
})->name('instrument.select.api');

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
    foreach (config('app.available_locales') as $lang => $key) {
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

// Eyepiece select helper that correctly returns any selected eyepieces (supports arrays)
Route::get('eyepiece.select.api', function (Request $request) {

    $allEyepieces = [];

    // Support an alternative comma-separated parameter `selected_ids`.
    // Some callers (Blade attributes/HTML) may end up with HTML-escaped ampersands
    // which break array-style querystrings like selected[0]=..&selected[1]=..
    // Accept `selected_ids=1,2,3` and normalize it to `selected` so the
    // existing code below can continue to work unchanged.
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $ids = array_filter(array_map('trim', explode(',', $request->selected_ids)));
        if (!empty($ids)) {
            $request->merge(['selected' => $ids]);
        }
    }

    // Show the selected options (supports single value or array)
    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && ($request->search == '' || $request->search === null)) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $ep = Eyepiece::where('id', $sel)->first();
            if ($ep) {
                $allEyepieces[] = [
                    'id' => $ep->id,
                    'name' => $ep->name,
                    'description' => $ep->description,
                ];
            }
        }

        return $allEyepieces;
    }

    // Otherwise return selected first + the full (or filtered) list
    if ($request->exists('selected')) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $ep = Eyepiece::where('id', $sel)->first();
            if ($ep) {
                $allEyepieces[] = [
                    'id' => $ep->id,
                    'name' => $ep->name,
                    'description' => $ep->description,
                ];
            }
        }
    }

    // Get the eyepieces for the authenticated user (only active ones) and sort by focal length descending
    $eyepieces = Eyepiece::where('observer', auth()->user()->username)->where('active', 1)->get();
    // Sort numerically by focal_length_mm in descending order, nulls last
    $eyepieces = $eyepieces->sortByDesc(function ($e) {
        return $e->focal_length_mm === null ? -1 : (int) $e->focal_length_mm;
    });

    foreach ($eyepieces as $eyepiece) {
        if ($request->search == '' || Str::contains(Str::lower($eyepiece->fullName()), Str::lower($request->search))) {
            $allEyepieces[] = [
                'id' => $eyepiece->id,
                'name' => $eyepiece->name,
                'description' => $eyepiece->description,
            ];
        }
    }

    // Remove duplicates while preserving order
    $seen = [];
    $uniqueEyepieces = [];
    foreach ($allEyepieces as $eyepiece) {
        if (!isset($seen[$eyepiece['id']])) {
            $seen[$eyepiece['id']] = true;
            $uniqueEyepieces[] = $eyepiece;
        }
    }

    return $uniqueEyepieces;
})->name('eyepiece.select.api');


// Lens select helper that correctly returns any selected lenses (supports arrays)
Route::get('lens.select.api', function (Request $request) {

    $allLenses = [];

    // Support an alternative comma-separated parameter `selected_ids`.
    // Some callers (Blade attributes/HTML) may end up with HTML-escaped ampersands
    // which break array-style querystrings like selected[0]=..&selected[1]=..
    // Accept `selected_ids=1,2,3` and normalize it to `selected` so the
    // existing code below can continue to work unchanged.
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $ids = array_filter(array_map('trim', explode(',', $request->selected_ids)));
        if (!empty($ids)) {
            $request->merge(['selected' => $ids]);
        }
    }

    // Show the selected options (supports single value or array)
    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && ($request->search == '' || $request->search === null)) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $lns = Lens::where('id', $sel)->first();
            if ($lns) {
                $allLenses[] = [
                    'id' => $lns->id,
                    'name' => $lns->fullName(),
                    'description' => $lns->description,
                ];
            }
        }

        return $allLenses;
    }

    // Otherwise return selected first + the full (or filtered) list
    if ($request->exists('selected')) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $lns = Lens::where('id', $sel)->first();
            if ($lns) {
                $allLenses[] = [
                    'id' => $lns->id,
                    'name' => $lns->fullName(),
                    'description' => $lns->description,
                ];
            }
        }
    }

    // Get the lenses for the authenticated user (only active ones) and sort alphabetically
    $lenses = Lens::where('observer', auth()->user()->username)->where('active', 1)->get();
    $lenses = $lenses->sortBy(function ($ln) {
        return Str::lower($ln->fullName());
    });

    foreach ($lenses as $lens) {
        if ($request->search == '' || Str::contains(Str::lower($lens->fullName()), Str::lower($request->search))) {
            $allLenses[] = [
                'id' => $lens->id,
                'name' => $lens->fullName(),
                'description' => $lens->description,
            ];
        }
    }

    // Remove duplicates while preserving order
    $seen = [];
    $uniqueLenses = [];
    foreach ($allLenses as $lens) {
        if (!isset($seen[$lens['id']])) {
            $seen[$lens['id']] = true;
            $uniqueLenses[] = $lens;
        }
    }

    return $uniqueLenses;
})->name('lens.select.api');


// Filter select helper that correctly returns any selected filters (supports arrays)
Route::get('filter.select.api', function (Request $request) {

    $allFilters = [];

    // Support an alternative comma-separated parameter `selected_ids`.
    // Some callers (Blade attributes/HTML) may end up with HTML-escaped ampersands
    // which break array-style querystrings like selected[0]=..&selected[1]=..
    // Accept `selected_ids=1,2,3` and normalize it to `selected` so the
    // existing code below can continue to work unchanged.
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $ids = array_filter(array_map('trim', explode(',', $request->selected_ids)));
        if (!empty($ids)) {
            $request->merge(['selected' => $ids]);
        }
    }

    // Show the selected options (supports single value or array)
    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && ($request->search == '' || $request->search === null)) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $filter = Filter::where('id', $sel)->first();
            if ($filter) {
                $allFilters[] = [
                    'id' => $filter->id,
                    'name' => $filter->fullName(),
                    'description' => $filter->description,
                ];
            }
        }

        return $allFilters;
    }

    // Otherwise return selected first + the full (or filtered) list
    if ($request->exists('selected')) {
        $selected = $request->selected;
        if (!is_array($selected)) {
            $selected = [$selected];
        }
        foreach ($selected as $sel) {
            $filter = Filter::where('id', $sel)->first();
            if ($filter) {
                $allFilters[] = [
                    'id' => $filter->id,
                    'name' => $filter->fullName(),
                    'description' => $filter->description,
                ];
            }
        }
    }

    // Get the filters for the authenticated user (only active ones) and sort alphabetically
    $filters = Filter::where('observer', auth()->user()->username)->where('active', 1)->get();
    $filters = $filters->sortBy(function ($f) {
        return Str::lower($f->fullName());
    });

    foreach ($filters as $filter) {
        if ($request->search == '' || Str::contains(Str::lower($filter->fullName()), Str::lower($request->search))) {
            $allFilters[] = [
                'id' => $filter->id,
                'name' => $filter->fullName(),
                'description' => $filter->description,
            ];
        }
    }

    // Remove duplicates while preserving order
    $seen = [];
    $uniqueFilters = [];
    foreach ($allFilters as $filter) {
        if (!isset($seen[$filter['id']])) {
            $seen[$filter['id']] = true;
            $uniqueFilters[] = $filter;
        }
    }

    return $uniqueFilters;
})->name('filter.select.api');


Route::get('/instrument/{userid}', [InstrumentController::class, 'show_from_user']);
Route::get('/eyepieces/{userid}', [EyepieceController::class, 'show_from_user']);
Route::get('/lenses/{userid}', [LensController::class, 'show_from_user']);
Route::get('/filters/{userid}', [FilterController::class, 'show_from_user']);
