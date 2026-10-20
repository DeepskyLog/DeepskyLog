<?php

use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\EyepieceController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\LensController;
use App\Http\Controllers\ObjectController;
use App\Models\Atlas;
use App\Models\Eyepiece;
use App\Models\EyepieceMake;
use App\Models\EyepieceType;
use App\Models\Filter;
use App\Models\FilterColor;
use App\Models\FilterMake;
use App\Models\FilterType;
use App\Models\Instrument;
use App\Models\InstrumentMake;
use App\Models\InstrumentSet;
use App\Models\InstrumentType;
use App\Models\Lens;
use App\Models\LensMake;
use App\Models\Location;
use App\Models\MountType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Endpoints that read the logged-in user (their equipment, locations,
| profile settings) need 'auth:sanctum': the site's own pages are
| authenticated by their session cookie, guests get a 401. Object search,
| users.select.api (the observation search is open to guests; guests only
| get observers with observations) and the documented
| /api/{instrument,eyepieces,lenses,filters}/{userid} stay public.
| /api/v1/me/... is the token API for programs (App\Http\Controllers\Api\MeController).
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('search', [SearchController::class, 'search'])->name('api.search');
Route::get('objects.search', [ObjectController::class, 'searchObjects'])->name('api.objects.search');
Route::get('constellation-from-coords', [ObjectController::class, 'getConstellationFromCoords'])->name('api.constellation-from-coords');
Route::get('objects/{slug}/simbad-data', [ObjectController::class, 'fetchSimbadData'])->name('api.objects.simbad-data')->middleware('auth');
// Lookup SIMBAD by arbitrary name (used by object creation wizard)
Route::get('objects/simbad-lookup', [ObjectController::class, 'simbadLookupByName'])->name('api.objects.simbad-lookup')->middleware('auth');

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
})->name('countries.index')->middleware('auth:sanctum');

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
})->name('licenses.index')->middleware('auth:sanctum');

Route::get('locations.api', function (Request $request) {
    $allLocations = [];
    // Show the selected option(s) when provided. Support single value or array.
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
                    'description' => $loc->description ?? '',
                ];
            }
        }
    }

    // If an instrument_set is provided, try to return only locations that belong to that set
    if ($request->filled('instrument_set')) {
        $set = InstrumentSet::where('id', $request->instrument_set)->first();
        if ($set) {
            $locations = $set->locations()->where('active', 1)->get();
            if ($locations->isNotEmpty()) {
                foreach ($locations as $location) {
                    if ($request->search == '' || Str::contains(Str::lower($location->name), Str::lower($request->search))) {
                        $allLocations[] = [
                            'id' => $location->id,
                            'name' => $location->name,
                            'description' => $location->description ?? '',
                        ];
                    }
                }
                return $allLocations;
            }
            // empty set -> fall through to global list
        }
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
})->name('locations.api')->middleware('auth:sanctum');

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
        if ($request->search == '' || Str::contains(Str::lower(html_entity_decode($location->name)), Str::lower($request->search))) {
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
})->name('location.select.api')->middleware('auth:sanctum');

Route::get('instrument.api', function (Request $request) {
    $allInstruments = [];
    // Show the selected option(s); without a value, the standard instrument
    if ($request->exists('selected')) {
        $selected = array_filter((array) $request->selected, fn ($sel) => $sel !== null && $sel !== '');
        if (empty($selected)) {
            $selected = [auth()->user()->stdtelescope];
        }
        foreach ($selected as $sel) {
            $instrument = Instrument::where('id', $sel)->first();
            if ($instrument) {
                $allInstruments[] = [
                    'id' => $instrument->id,
                    'name' => $instrument->fullName(),
                    'description' => $instrument->description,
                ];
            }
        }
    }

    // If an instrument_set is provided, try to return only instruments that belong to that set
    if ($request->filled('instrument_set')) {
        $set = InstrumentSet::where('id', $request->instrument_set)->first();
        if ($set) {
            $instruments = $set->instruments()->where('active', 1)->get();
            if ($instruments->isNotEmpty()) {
                foreach ($instruments as $instrument) {
                    if ($request->search == '' || Str::contains(Str::lower($instrument->fullName()), Str::lower($request->search))) {
                        $allInstruments[] = [
                            'id' => $instrument->id,
                            'name' => $instrument->fullName(),
                            'description' => $instrument->description,
                        ];
                    }
                }
                return $allInstruments;
            }
            // empty set -> fall through to global list
        }
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
})->name('instrument.api')->middleware('auth:sanctum');

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
})->name('instrumentset.api')->middleware('auth:sanctum');

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
})->name('instrumentset.select.api')->middleware('auth:sanctum');

// Instrument select helper that correctly returns any selected instruments (supports arrays)
Route::get('instrument.select.api', function (Request $request) {
    // The observer's own instruments carry the lens that is always used with
    // them, which the observation form then selects with the instrument.
    $lensNames = \App\Models\Lens::with('lens_make')->where('user_id', auth()->id())->get()
        ->mapWithKeys(fn ($lens) => [$lens->id => html_entity_decode($lens->fullName())]);
    $option = function (Instrument $instrument) use ($lensNames) {
        $option = ['id' => $instrument->id, 'name' => $instrument->fullName()];
        if ((int) $instrument->user_id === (int) auth()->id()) {
            $lensId = $instrument->lens_id && isset($lensNames[$instrument->lens_id]) ? (int) $instrument->lens_id : null;
            $option['lens_id'] = $lensId;
            $option['lens_name'] = $lensId ? $lensNames[$lensId] : null;
        }

        return $option;
    };

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
                $allInstruments[] = $option($inst);
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
                $allInstruments[] = $option($inst);
            }
        }
    }

    // Get the instruments for the authenticated user (only active ones).
    // Sort by aperture (diameter) descending so largest diameters appear first.
    $instruments = Instrument::where('observer', auth()->user()->username)->where('active', 1)->get();
    $instruments = $instruments->sortByDesc(function ($i) {
        return $i->aperture_mm ?? 0;
    })->values();

    // If an instrument_set is provided, try to return only instruments that belong to that set
    if ($request->filled('instrument_set')) {
        $set = InstrumentSet::where('id', $request->instrument_set)->first();
        if ($set) {
            $setInstruments = $set->instruments()->where('active', 1)->get();
            if ($setInstruments->isNotEmpty()) {
                // Sort by aperture descending (largest first)
                $setInstruments = $setInstruments->sortByDesc(function ($i) {
                    return $i->aperture_mm ?? 0;
                })->values();
                foreach ($setInstruments as $instrument) {
                    if ($request->search == '' || Str::contains(Str::lower($instrument->fullName()), Str::lower($request->search))) {
                        $allInstruments[] = $option($instrument);
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
            }
            // empty set -> fall through to global list
        }
    }

    foreach ($instruments as $instrument) {
        if ($request->search == '' || Str::contains(Str::lower($instrument->fullName()), Str::lower($request->search))) {
            $allInstruments[] = $option($instrument);
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
})->name('instrument.select.api')->middleware('auth:sanctum');

Route::get('atlas.index', function (Request $request) {
    $allAtlases = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $atlas = Atlas::where('code', auth()->user()->standardAtlasCode)->first();
        if ($atlas) {
            $allAtlases[] = [
                'id' => $atlas->code,
                'name' => $atlas->name,
            ];
        }
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
})->name('atlas.index')->middleware('auth:sanctum');

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
})->name('ui_languages.index')->middleware('auth:sanctum');

// Make a list of all possible languages for the observations in DeepskyLog.
Route::get('observation_languages.index', function (Request $request) {
    $allLanguages = [];
    // Show the selected option (use provided value, fallback to auth user)
    if ($request->exists('selected')) {
        $sel = $request->selected;
        if (empty($sel) && auth()->check()) {
            $sel = auth()->user()->observationlanguage;
        }
        if (!empty($sel)) {
            $allLanguages[] = [
                'id' => $sel,
                'name' => Languages::lookup([$sel], 'mixed')->values()[0],
            ];
        }
    }

    // Get the languages
    foreach (Languages::lookup(config('app.observation_languages'), 'mixed') as $key => $language) {
        if ($request->search == '' || Str::contains(Str::lower($language), Str::lower($request->search))) {
            $allLanguages[] = [
                'id' => $key,
                'name' => $language,
            ];
        }
    }

    return $allLanguages;
})->name('observation_languages.index')->middleware('auth:sanctum');

// Make a list of all users to add to a team.
// The list contains the e-mail addresses of all users, so only administrators
// get it (any user can create and own a team, so ownership is not enough).
Route::get('addUserToTeam.index', function (Request $request) {
    abort_unless($request->user()->hasAdministratorPrivileges(), 403);

    $allUsers = [];

    // Get the languages
    foreach (User::all() as $user) {
        if ($request->search == '' || Str::contains(Str::lower($user->name), Str::lower($request->search)) || Str::contains(Str::lower($user->email), Str::lower($request->search))) {
            $allUsers[] = [
                'id' => $user->email,
                'name' => $user->name . ' (' . $user->email . ')',
            ];
        }
    }

    return $allUsers;
})->name('addUserToTeam.index')->middleware('auth:sanctum');

// Users select helper for the message receiver, the session's other
// observers and the observer filter of the observation search (returns the
// username as id and the full name). The observation search is open to
// guests, so guests only get observers with observations, who are listed on
// /observers anyway. Users are only looked up by username, never by e-mail
// address, so the endpoint does not tell which addresses are registered. The
// search runs in the database and returns at most $limit users.
Route::get('users.select.api', function (Request $request) {
    $limit = 50;
    $loggedIn = $request->user('sanctum') !== null;

    $query = User::query()->select(['id', 'name', 'username']);
    if (!$loggedIn) {
        $query->whereExists(function ($q) {
            $q->selectRaw('1')->from('observations')->whereColumn('observations.observer_username', 'users.username');
        });
    }

    // Support comma-separated selected_ids parameter used by some callers
    $selected = $request->input('selected', []);
    if (!$request->exists('selected') && $request->filled('selected_ids')) {
        $selected = explode(',', (string) $request->selected_ids);
    }
    $selected = array_values(array_filter(array_map('trim', array_map('strval', array_filter((array) $selected, 'is_scalar')))));
    $selected = array_slice($selected, 0, $limit);

    $selectedUsers = collect();
    if ($selected !== []) {
        $selectedUsers = (clone $query)
            ->whereIn('username', $selected)
            ->get();
    }

    $search = trim((string) $request->input('search', ''));

    // If selected is provided and there's no search term, return only the selected items
    if ($request->exists('selected') && $search === '') {
        return $selectedUsers->map(fn ($u) => ['id' => $u->username, 'name' => $u->name])->values();
    }

    // Otherwise return selected first + the (filtered) list, sorted by full name
    if ($search !== '') {
        $like = '%' . addcslashes($search, '%_\\') . '%';
        $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('username', 'like', $like));
    }
    $users = $query->orderBy('name')->limit($limit)->get();

    return $selectedUsers->concat($users)
        ->unique('username')
        ->map(fn ($u) => ['id' => $u->username, 'name' => $u->name])
        ->values();
})->name('users.select.api');

Route::get('instrument_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = InstrumentMake::where('id', $request->selected)->first();
        if ($item) {
            $allMakes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
    }
    $makes = InstrumentMake::orderBy('name')->get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('instrument_makes.api')->middleware('auth:sanctum');

Route::get('instrument_types.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = InstrumentType::where('id', $request->selected)->first();
        if ($item) {
            $allTypes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
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
})->name('instrument_types.api')->middleware('auth:sanctum');

Route::get('mount_types.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = MountType::where('id', $request->selected)->first();
        if ($item) {
            $allTypes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }

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
})->name('mount_types.api')->middleware('auth:sanctum');

Route::get('eyepiece_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = EyepieceMake::where('id', $request->selected)->first();
        if ($item) {
            $allMakes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
    }
    $makes = EyepieceMake::orderBy('name')->get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('eyepiece_makes.api')->middleware('auth:sanctum');

Route::get('eyepiece_type.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = EyepieceType::where('id', $request->selected)->first();
        if ($item) {
            $allTypes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
    }
    $allTypes[] = [
        'id' => 0,
        'name' => '',
    ];
    // Limit to the types of the given make; without a make, all types
    $types = $request->exists('make')
        ? EyepieceType::where('eyepiece_makes_id', $request->make)->orderBy('name')->get()
        : EyepieceType::orderBy('name')->get();
    foreach ($types as $type) {
        if ($request->search == '' || Str::contains(Str::lower($type->name), Str::lower($request->search))) {
            $allTypes[] = [
                'id' => $type->id,
                'name' => $type->name,
            ];
        }
    }

    return $allTypes;
})->name('eyepiece_types.api')->middleware('auth:sanctum');

Route::get('lens_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = LensMake::where('id', $request->selected)->first();
        if ($item) {
            $allMakes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
    }
    $makes = LensMake::orderBy('name')->get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('lens_makes.api')->middleware('auth:sanctum');

Route::get('filter_makes.api', function (Request $request) {
    $allMakes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = FilterMake::where('id', $request->selected)->first();
        if ($item) {
            $allMakes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
    }
    $makes = FilterMake::orderBy('name')->get();
    foreach ($makes as $make) {
        if ($request->search == '' || Str::contains(Str::lower($make->name), Str::lower($request->search))) {
            $allMakes[] = [
                'id' => $make->id,
                'name' => $make->name,
            ];
        }
    }

    return $allMakes;
})->name('filter_makes.api')->middleware('auth:sanctum');

Route::get('filter_colors.api', function (Request $request) {
    $allColors = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = FilterColor::where('id', $request->selected)->first();
        if ($item) {
            $allColors[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
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
})->name('filter_colors.api')->middleware('auth:sanctum');

Route::get('filter_types.api', function (Request $request) {
    $allTypes = [];
    // Show the selected option
    if ($request->exists('selected')) {
        $item = FilterType::where('id', $request->selected)->first();
        if ($item) {
            $allTypes[] = [
                'id' => $item->id,
                'name' => $item->name,
            ];
        }
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
})->name('filter_types.api')->middleware('auth:sanctum');

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
                    'name' => $ep->fullName(),
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
                    'name' => $ep->fullName(),
                ];
            }
        }
    }

    // If an instrument_set is provided, try to return only eyepieces that belong to that set
    if ($request->filled('instrument_set')) {
        $set = InstrumentSet::where('id', $request->instrument_set)->first();
        if ($set) {
            $eyepieces = $set->eyepieces()->where('active', 1)->get();
            if ($eyepieces->isNotEmpty()) {
                // Sort numerically by focal_length_mm in descending order, nulls last
                $eyepieces = $eyepieces->sortByDesc(function ($e) {
                    return $e->focal_length_mm === null ? -1 : (int) $e->focal_length_mm;
                });
                foreach ($eyepieces as $eyepiece) {
                    if ($request->search == '' || Str::contains(Str::lower($eyepiece->fullName()), Str::lower($request->search))) {
                        $allEyepieces[] = [
                            'id' => $eyepiece->id,
                            'name' => $eyepiece->fullName(),
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
            }
            // empty set -> fall through to global list
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
                'name' => $eyepiece->fullName(),
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
})->name('eyepiece.select.api')->middleware('auth:sanctum');

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
                ];
            }
        }
    }

    // Get the lenses for the authenticated user (only active ones) and sort alphabetically
    $lenses = Lens::where('observer', auth()->user()->username)->where('active', 1)->get();
    $lenses = $lenses->sortBy(function ($ln) {
        return Str::lower($ln->fullName());
    });

    // If an instrument_set is provided, try to return only lenses that belong to that set
    if ($request->filled('instrument_set')) {
        $set = InstrumentSet::where('id', $request->instrument_set)->first();
        if ($set) {
            $setLenses = $set->lenses()->where('active', 1)->get();
            if ($setLenses->isNotEmpty()) {
                $setLenses = $setLenses->sortBy(function ($ln) {
                    return Str::lower($ln->fullName());
                });
                foreach ($setLenses as $lens) {
                    if ($request->search == '' || Str::contains(Str::lower($lens->fullName()), Str::lower($request->search))) {
                        $allLenses[] = [
                            'id' => $lens->id,
                            'name' => $lens->fullName(),
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
            }
            // empty set -> fall through to global list
        }
    }

    foreach ($lenses as $lens) {
        if ($request->search == '' || Str::contains(Str::lower($lens->fullName()), Str::lower($request->search))) {
            $allLenses[] = [
                'id' => $lens->id,
                'name' => $lens->fullName(),
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
})->name('lens.select.api')->middleware('auth:sanctum');

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
})->name('filter.select.api')->middleware('auth:sanctum');

Route::get('/instrument/{userid}', [InstrumentController::class, 'show_from_user']);
Route::get('/eyepieces/{userid}', [EyepieceController::class, 'show_from_user']);
Route::get('/lenses/{userid}', [LensController::class, 'show_from_user']);
Route::get('/filters/{userid}', [FilterController::class, 'show_from_user']);

// Read-only API on the data of the authenticated observer (pydeepskylog and
// other programs): a personal access token with the 'read' ability, made on
// /user/api-tokens and sent as "Authorization: Bearer <token>".
Route::prefix('v1/me')->middleware(['auth:sanctum', 'abilities:read'])->name('api.me.')->group(function () {
    Route::get('/', [MeController::class, 'profile'])->name('profile');
    Route::get('locations', [MeController::class, 'locations'])->name('locations');
    Route::get('instruments', [MeController::class, 'instruments'])->name('instruments');
    Route::get('eyepieces', [MeController::class, 'eyepieces'])->name('eyepieces');
    Route::get('lenses', [MeController::class, 'lenses'])->name('lenses');
    Route::get('filters', [MeController::class, 'filters'])->name('filters');
    Route::get('instrument-sets', [MeController::class, 'instrumentSets'])->name('instrument-sets');
    Route::get('observations', [MeController::class, 'observations'])->name('observations');
    Route::get('observations/{id}', [MeController::class, 'observation'])->whereNumber('id')->name('observation');
    Route::get('sessions', [MeController::class, 'sessions'])->name('sessions');
    Route::get('sessions/{id}', [MeController::class, 'session'])->whereNumber('id')->name('session');
    Route::get('observing-lists', [MeController::class, 'observingLists'])->name('observing-lists');
    Route::get('observing-lists/{id}', [MeController::class, 'observingList'])->whereNumber('id')->name('observing-list');
});

// Persist Aladin preview defaults for the authenticated user
Route::middleware('auth:sanctum')->post('user/aladin-defaults', function (Request $request) {
    $user = $request->user();
    if (!$user)
        return response()->json(['error' => 'Unauthenticated'], 401);

    $data = $request->only(['instrument_id', 'eyepiece_id', 'lens_id']);
    try {
        // Instrument: prefer stdtelescope column if present
        if (!empty($data['instrument_id']) && $data['instrument_id'] != 0) {
            $inst = \App\Models\Instrument::where('id', $data['instrument_id'])->where('observer', $user->username)->first();
            if (!$inst)
                return response()->json(['error' => 'Invalid instrument'], 422);
            if (Schema::hasColumn('users', 'stdtelescope')) {
                $user->stdtelescope = $inst->id;
            }
        } else {
            if (Schema::hasColumn('users', 'stdtelescope')) {
                $user->stdtelescope = null;
            }
        }

        // Eyepiece
        if (!empty($data['eyepiece_id']) && $data['eyepiece_id'] != 0) {
            $ep = \App\Models\Eyepiece::where('id', $data['eyepiece_id'])->where('observer', $user->username)->first();
            if (!$ep)
                return response()->json(['error' => 'Invalid eyepiece'], 422);
            if (Schema::hasColumn('users', 'stdeyepiece')) {
                $user->stdeyepiece = $ep->id;
            } else if (Schema::hasColumn('users', 'preferences')) {
                $prefs = $user->preferences ?? [];
                $prefs['aladin_default_eyepiece'] = $ep->id;
                $user->preferences = $prefs;
            }
        } else {
            if (Schema::hasColumn('users', 'stdeyepiece')) {
                $user->stdeyepiece = null;
            }
            if (Schema::hasColumn('users', 'preferences')) {
                $prefs = $user->preferences ?? [];
                unset($prefs['aladin_default_eyepiece']);
                $user->preferences = $prefs;
            }
        }

        // Lens: the lens of the telescope saved as default (the observer has
        // no standard lens of their own; a telescope has the lens that is
        // always used with it).
        $lens = null;
        if (!empty($data['lens_id']) && $data['lens_id'] != 0) {
            $lens = \App\Models\Lens::where('id', $data['lens_id'])->where('user_id', $user->id)->first();
            if (!$lens)
                return response()->json(['error' => 'Invalid lens'], 422);
        }
        if (Schema::hasColumn('users', 'preferences')) {
            $prefs = $user->preferences ?? [];
            unset($prefs['aladin_default_lens']);
            $user->preferences = $prefs;
        }
        $telescope = $user->stdtelescope ? \App\Models\Instrument::where('id', $user->stdtelescope)->where('user_id', $user->id)->first() : null;
        if ($telescope && (int) $telescope->lens_id !== (int) $lens?->id) {
            $telescope->update(['lens_id' => $lens?->id]);
        }

        $user->save();
        return response()->json(['ok' => true]);
    } catch (\Exception $ex) {
        return response()->json(['error' => 'Save failed', 'message' => $ex->getMessage()], 500);
    }
})->name('user.aladin-defaults.save');

// Compute magnification client-side without a Livewire round-trip
Route::get('magnification', function (Request $request) {
    $instrumentId = $request->query('instrument_id');
    $eyepieceId   = $request->query('eyepiece_id');
    $lensId       = $request->query('lens_id');

    $instrument = $instrumentId ? \App\Models\Instrument::find($instrumentId) : null;
    if (! $instrument) {
        return response()->json(['magnification' => null]);
    }

    $eyepiece = $eyepieceId ? \App\Models\Eyepiece::find($eyepieceId) : null;
    $lens     = $lensId ? \App\Models\Lens::find($lensId) : null;

    return response()->json(['magnification' => $instrument->magnificationFor($eyepiece, $lens)]);
})->name('magnification.compute')->middleware('auth:sanctum');
