<?php

use App\Models\ObservationSession;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Use the SessionController homepage action so the welcome view receives the expected `$sessions` variable.
    Route::get('/dashboard', [App\Http\Controllers\SessionController::class, 'homepage'])->name('dashboard');
});

// Override Jetstream's current-team.update route to redirect back after switching teams
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::put('/current-team', function (\Illuminate\Http\Request $request) {
        $team = Laravel\Jetstream\Jetstream::newTeamModel()->findOrFail($request->team_id);

        if (!$request->user()->switchTeam($team)) {
            abort(403);
        }

        // Redirect back to the referring page so the header renders the updated team immediately.
        return redirect()->back(303);
    })->name('current-team.update');

    // Provide a simple GET link that switches the current team and redirects back.
    // This is intended for use in navigation/dropdowns where a normal link (full
    // navigation) avoids client-side JS interceptors turning the request into an
    // XHR/fetch which would not automatically follow the server redirect.
    Route::get('/switch-team/{team}', function (\Illuminate\Http\Request $request, $team) {
        $teamModel = Laravel\Jetstream\Jetstream::newTeamModel()->findOrFail($team);

        if (!$request->user()->switchTeam($teamModel)) {
            abort(403);
        }

        return redirect()->back();
    })->name('current-team.switch');
});

// Switch language
Route::get('language/{locale}', function ($locale) {
    app()->setLocale($locale);
    session()->put('locale', $locale);

    return redirect()->back();
});

// Teams
Route::get('/teams/{team}', 'App\Http\Controllers\DeepskyLogTeamController@show')->name('teams.show');

// Observers
Route::get('/observers/admin', 'App\Http\Controllers\ObserverController@admin')->name('observers.admin')->can('delete', User::class);
Route::get('/admin/check-objects', [App\Http\Controllers\AdminObjectCheckController::class, 'index'])
    ->middleware('auth')
    ->name('admin.objects.check');
Route::post('/admin/check-objects/orphan-objectnames', [App\Http\Controllers\AdminObjectCheckController::class, 'destroyOrphanObjectNames'])
    ->middleware('auth')
    ->name('admin.objects.check.cleanup');
Route::post('/admin/check-objects/repair-constellations', [App\Http\Controllers\AdminObjectCheckController::class, 'repairConstellations'])
    ->middleware('auth')
    ->name('admin.objects.check.repair');
Route::get('/admin/check-objects/export/constellation-mismatches', [App\Http\Controllers\AdminObjectCheckController::class, 'exportConstellationMismatches'])
    ->middleware('auth')
    ->name('admin.objects.check.export-constellations');
Route::get('/admin/check-objects/export/orphan-objectnames', [App\Http\Controllers\AdminObjectCheckController::class, 'exportOrphanObjectNames'])
    ->middleware('auth')
    ->name('admin.objects.check.export-orphans');
Route::get('/admin/check-objects/export/observation-alias-mappings', [App\Http\Controllers\AdminObjectCheckController::class, 'exportAliasFixableObservationMappings'])
    ->middleware('auth')
    ->name('admin.objects.check.export-observation-alias-mappings');
Route::post('/admin/check-objects/repair-observation-objectnames', [App\Http\Controllers\AdminObjectCheckController::class, 'repairObservationObjectNames'])
    ->middleware('auth')
    ->name('admin.objects.check.repair-observation-objectnames');
Route::get('/observers/{observer}', 'App\Http\Controllers\ObserverController@show')->name('observer.show');

// RSS feeds
Route::get('/feed/observations', [App\Http\Controllers\RssFeedController::class, 'observations'])->name('feed.observations');
Route::get('/feed/drawings', [App\Http\Controllers\RssFeedController::class, 'drawings'])->name('feed.drawings');
Route::get('/feed/observing-lists', [App\Http\Controllers\RssFeedController::class, 'observingLists'])->name('feed.observing-lists');

// Drawings
Route::get('/drawings', 'App\Http\Controllers\DrawingController@index')->name('drawings.index');
Route::get('/cometdrawings', 'App\Http\Controllers\CometDrawingController@index')->name('cometdrawings.index');
Route::get('/drawings/{observer}', 'App\Http\Controllers\DrawingController@show')->name('drawings.show');
// Support object-scoped comet drawings and observer+object comet drawings
Route::get('/cometdrawings/{observer}/{object}', 'App\Http\Controllers\CometDrawingController@showObserverObject')->name('cometdrawings.user.object');
// Per-observer comet drawings (keep after the observer+object route to avoid capture)
Route::get('/cometdrawings/{observer}', 'App\Http\Controllers\CometDrawingController@show')->name('cometdrawings.show');
// Add observation selection and create pages
Route::get('/observation/add/select', [App\Http\Controllers\ObservationsController::class, 'select'])->name('observation.add.select')->middleware('auth');
Route::get('/observation/add', [App\Http\Controllers\ObservationsController::class, 'create'])->name('observation.create')->middleware('auth');
Route::post('/observation', [App\Http\Controllers\ObservationsController::class, 'store'])->name('observation.store')->middleware('auth');
// Import observations (CSV / OAL XML)
Route::get('/observation/import', [App\Http\Controllers\ObservationImportController::class, 'showForm'])->name('observation.import')->middleware('auth');
Route::post('/observation/import', [App\Http\Controllers\ObservationImportController::class, 'import'])->name('observation.import.process')->middleware('auth');
// Observations (deepsky + comet)
Route::get('/observations', 'App\Http\Controllers\ObservationsController@index')->name('observations.index');
Route::get('/observations/search', [App\Http\Controllers\ObservationsController::class, 'search'])->name('observations.search');
Route::get('/observations/search/objects', [App\Http\Controllers\ObservationsController::class, 'searchObjects'])
    ->name('observations.search.objects')
    ->middleware('doNotCacheResponse');
Route::get('/observations/search/export.pdf', [App\Http\Controllers\ObservationSearchExportController::class, 'pdf'])
    ->name('observations.search.pdf')
    ->middleware('doNotCacheResponse');
Route::get('/observations/search/export.skylist', [App\Http\Controllers\ObservationSearchExportController::class, 'skylist'])
    ->name('observations.search.skylist')
    ->middleware('doNotCacheResponse');
Route::get('/observations/search/export.obs', [App\Http\Controllers\ObservationSearchExportController::class, 'obs'])
    ->name('observations.search.obs')
    ->middleware('doNotCacheResponse');
Route::get('/observations/search/export.apd', [App\Http\Controllers\ObservationSearchExportController::class, 'apd'])
    ->name('observations.search.apd')
    ->middleware('doNotCacheResponse');
Route::get('/observations/search/export.oal.xml', [App\Http\Controllers\ObservationSearchExportController::class, 'oal'])
    ->name('observations.search.oal')
    ->middleware('doNotCacheResponse');
Route::get('/observation/{observer}/{date}/{object}/{index?}', [App\Http\Controllers\ObservationsController::class, 'detail'])
    ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->where('index', '[0-9]+')
    ->name('observations.detail');
// Edit and delete a single observation (owner or admin only)
Route::get('/observation/{observer}/{date}/{object}/{index?}/edit', [App\Http\Controllers\ObservationsController::class, 'edit'])
    ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->where('index', '[0-9]+')
    ->name('observations.edit')
    ->middleware(['auth', 'verified']);

Route::delete('/observation/{observer}/{date}/{object}/{index?}', [App\Http\Controllers\ObservationsController::class, 'destroy'])
    ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->where('index', '[0-9]+')
    ->name('observations.destroy')
    ->middleware(['auth', 'verified']);
Route::get('/observation/{observer}/{date}/{object}/{index?}/compare-my-last', [App\Http\Controllers\ObservationsController::class, 'compareWithMyLast'])
    ->middleware('auth')
    ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->where('index', '[0-9]+')
    ->name('observations.compare.my-last');
// Object-scoped drawings listing (e.g. /observations/drawings/m-31)
Route::get('/observations/drawings/{slug}', [App\Http\Controllers\ObservationsController::class, 'showObjectDrawings'])->name('observations.drawings.show');
// Object-scoped observations listing (e.g. /observations/object/m-31)
Route::get('/observations/object/{slug}', [App\Http\Controllers\ObservationsController::class, 'showObject'])->name('observations.object');
// Observations for a specific observer filtered by object (e.g. /observations/wim-de-meester/m-31)
Route::get('/observations/{observer}/{object}', [App\Http\Controllers\ObservationsController::class, 'showObserverObject'])->name('observations.user.object');
// Drawings for a specific observer filtered by object (e.g. /observations/drawings/wim-de-meester/m-31)
Route::get('/observations/drawings/{observer}/{object}', [App\Http\Controllers\ObservationsController::class, 'showObserverObjectDrawings'])->name('observations.drawings.user.object');
Route::get('/observations/{observer}', 'App\Http\Controllers\ObservationsController@show')->name('observations.show');
// Separate comet observations pages
// Comet observations: support object-scoped pages and observer pages
Route::get('/cometobservations/{observer}/{object}', 'App\\Http\\Controllers\\ObservationsController@cometShowObserverObject')->name('observations.comet.user.object');
Route::get('/cometobservations/{object}', 'App\\Http\\Controllers\\ObservationsController@cometIndexByObject')->name('observations.comet.object');
Route::get('/cometobservations', 'App\\Http\\Controllers\\ObservationsController@cometIndex')->name('observations.comet.index');
Route::get('/cometobservations/{observer}', 'App\\Http\\Controllers\\ObservationsController@cometShow')->name('observations.comet.show');

// Sketch of the week / month
Route::get('/sketch-of-the-week', function () {
    return view(
        'sketch-of-the-week-month',
        [
            'sketches' => SketchOfTheWeek::orderBy('date', 'desc')->paginate(20),
            'week_month' => __('Week')
        ]
    );
})->name('sketch-of-the-week');
Route::get('/sketch-of-the-month', function () {
    return view(
        'sketch-of-the-week-month',
        [
            'sketches' => SketchOfTheMonth::orderBy('date', 'desc')->paginate(20),
            'week_month' => __('Month')
        ]
    );
})->name('sketch-of-the-month');
Route::get('/sketch-of-the-week/create', 'App\Http\Controllers\SketchOfTheWeekController@create')->name('sketch-of-the-week.create')->can('add_sketch', User::class);
Route::post('/sketch-of-the-week', 'App\Http\Controllers\SketchOfTheWeekController@store')->name('sketch-of-the-week.store')->can('add_sketch', User::class);
Route::delete('/sketch-of-the-week/{id}', 'App\Http\Controllers\SketchOfTheWeekController@destroy')->name('sketch-of-the-week.destroy')->middleware('auth');
Route::get('/sketch-of-the-month/create', 'App\Http\Controllers\SketchOfTheMonthController@create')->name('sketch-of-the-month.create')->can('add_sketch', User::class);
Route::post('/sketch-of-the-month', 'App\Http\Controllers\SketchOfTheMonthController@store')->name('sketch-of-the-month.store')->can('add_sketch', User::class);
Route::delete('/sketch-of-the-month/{id}', 'App\Http\Controllers\SketchOfTheMonthController@destroy')->name('sketch-of-the-month.destroy')->middleware('auth');

Route::get('/', [App\Http\Controllers\SessionController::class, 'homepage']);
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/sponsors', 'layouts.sponsors');
Route::view('/downloads/magazines', 'layouts.downloads.magazines');
Route::view('/downloads/dsl-image-catalogs', 'layouts.downloads.dsl_image_catalogs');
Route::view('/downloads/image-catalogs', 'layouts.downloads.image_catalogs');
Route::view('/downloads/atlases', 'layouts.downloads.atlases');
Route::view('/downloads/dsl-atlas', 'layouts.downloads.dsl_atlas');
Route::view('/downloads/forms', 'layouts.downloads.forms');

// Instruments
Route::resource(
    'instrument',
    'App\Http\Controllers\InstrumentController',
    ['parameters' => ['instrument' => 'instrument']]
)->middleware('verified')->except('show');

Route::get('/instrument/{user}/{instrument}', 'App\Http\Controllers\InstrumentController@show')
    ->name('instrument.show');

Route::get('/instrument/{user}/{instrument}/edit', 'App\Http\Controllers\InstrumentController@edit')
    ->name('instrument.edit')->middleware('auth');

Route::get('/admin/instrument', 'App\Http\Controllers\InstrumentController@indexAdmin')
    ->name('instrument.indexAdmin')->can('add_sketch', User::class);

Route::get('admin/instrument_make/{make}/edit', 'App\Http\Controllers\InstrumentController@editMake')
    ->name('instrument.editMake')->can('add_sketch', User::class);

Route::post('admin/instrument_make/store', 'App\Http\Controllers\InstrumentController@storeMake')
    ->name('instrumentmake.store')->can('add_sketch', User::class);

Route::post('admin/instrument_make/destroy', 'App\Http\Controllers\InstrumentController@destroyMake')
    ->name('instrumentmake.destroy')->can('add_sketch', User::class);

// Eyepieces
Route::resource(
    'eyepiece',
    'App\Http\Controllers\EyepieceController',
    ['parameters' => ['eyepiece' => 'eyepiece']]
)->middleware('verified')->except('show');

Route::get('/eyepiece/{user}/{eyepiece}', 'App\Http\Controllers\EyepieceController@show')
    ->name('eyepiece.show');

Route::get('/eyepiece/{user}/{eyepiece}/edit', 'App\Http\Controllers\EyepieceController@edit')
    ->name('eyepiece.edit')->middleware('auth');

Route::get('/admin/eyepiece', 'App\Http\Controllers\EyepieceController@indexAdmin')
    ->name('eyepiece.indexAdmin')->can('add_sketch', User::class);

Route::get('admin/eyepiece_make/{make}/edit', 'App\Http\Controllers\EyepieceController@editMake')
    ->name('eyepiecemake.edit')->can('add_sketch', User::class);

Route::post('admin/eyepiece_make/store', 'App\Http\Controllers\EyepieceController@storeMake')
    ->name('eyepiecemake.store')->can('add_sketch', User::class);

Route::post('admin/eyepiece_make/destroy', 'App\Http\Controllers\EyepieceController@destroyMake')
    ->name('eyepiecemake.destroy')->can('add_sketch', User::class);

Route::get('/admin/eyepiece-types', 'App\Http\Controllers\EyepieceController@indexTypeAdmin')
    ->name('eyepiece.index-typeAdmin')->can('add_sketch', User::class);

Route::get('admin/eyepiece_type/{type}/edit', 'App\Http\Controllers\EyepieceController@editType')
    ->name('eyepiecetype.edit')->can('add_sketch', User::class);

Route::post('admin/eyepiece_type/store', 'App\Http\Controllers\EyepieceController@storeType')
    ->name('eyepiecetype.store')->can('add_sketch', User::class);

Route::post('admin/eyepiece_type/destroy', 'App\Http\Controllers\EyepieceController@destroyType')
    ->name('eyepiecetype.destroy')->can('add_sketch', User::class);

// Lenses
Route::resource(
    'lens',
    'App\Http\Controllers\LensController',
    ['parameters' => ['lens' => 'lens']]
)->middleware('verified')->except('show');

Route::get('/lens/{user}/{lens}', 'App\Http\Controllers\LensController@show')
    ->name('lens.show');

Route::get('/lens/{user}/{lens}/edit', 'App\Http\Controllers\LensController@edit')
    ->name('lens.edit')->middleware('auth');

Route::get('/admin/lens', 'App\Http\Controllers\LensController@indexAdmin')
    ->name('lens.indexAdmin')->can('add_sketch', User::class);

Route::get('admin/lens_make/{make}/edit', 'App\Http\Controllers\LensController@editMake')
    ->name('lens.editMake')->can('add_sketch', User::class);

Route::post('admin/lens_make/store', 'App\Http\Controllers\LensController@storeMake')
    ->name('lensmake.store')->can('add_sketch', User::class);

Route::post('admin/lens_make/destroy', 'App\Http\Controllers\LensController@destroyMake')
    ->name('lensmake.destroy')->can('add_sketch', User::class);

// Filters
Route::resource(
    'filter',
    'App\Http\Controllers\FilterController',
    ['parameters' => ['filter' => 'filter']]
)->middleware('verified')->except('show');

Route::get('/filter/{user}/{filter}', 'App\Http\Controllers\FilterController@show')
    ->name('filter.show');

Route::get('/filter/{user}/{filter}/edit', 'App\Http\Controllers\FilterController@edit')
    ->name('filter.edit')->middleware('auth');

Route::get('/admin/filter', 'App\Http\Controllers\FilterController@indexAdmin')
    ->name('filter.indexAdmin')->can('add_sketch', User::class);

Route::get('admin/filter_make/{make}/edit', 'App\Http\Controllers\FilterController@editMake')
    ->name('filter.editMake')->can('add_sketch', User::class);

Route::post('admin/filter_make/store', 'App\Http\Controllers\FilterController@storeMake')
    ->name('filtermake.store')->can('add_sketch', User::class);

Route::post('admin/filter_make/destroy', 'App\Http\Controllers\FilterController@destroyMake')
    ->name('filtermake.destroy')->can('add_sketch', User::class);

// Locations
Route::resource(
    'location',
    'App\Http\Controllers\LocationController',
    ['parameters' => ['location' => 'location']]
)->middleware('verified')->except('show');

Route::get('/location/{user}/{location}', 'App\Http\Controllers\LocationController@show')
    ->name('location.show');

Route::get('/location/{user}/{location}/edit', 'App\Http\Controllers\LocationController@edit')
    ->name('location.edit')->middleware('auth');

// Instrument sets
Route::resource(
    'instrumentset',
    'App\Http\Controllers\InstrumentSetController',
    ['parameters' => ['instrumentset' => 'instrumentset']]
)->middleware('verified')->except('show');

Route::get('/instrumentset/{user}/{instrumentset}', 'App\Http\Controllers\InstrumentSetController@show')
    ->name('instrumentset.show');

// Observation sessions
Route::get('/session/{user}/{session}', 'App\Http\Controllers\SessionController@show')
    ->name('session.show')->middleware('doNotCacheResponse');
Route::get('/session/{user}/{session}/export.oal.xml', [App\Http\Controllers\ObservationSearchExportController::class, 'sessionOal'])
    ->name('session.export.oal')->middleware('doNotCacheResponse');

// Object detail pages: /object/{type}/{slugOrId}
// Specific Moon path: serve a Livewire-first Moon page at /object/moon
Route::get('/object/moon', function () {
    // Compute initial ephemerides using the same logic as the aside so
    // Livewire can hydrate immediately with correct values.
    try {
        $aside = new App\Http\Livewire\EphemerisAside();
        // Ensure mount() runs the calculations and populates properties
        if (method_exists($aside, 'mount'))
            $aside->mount();
        $payload = [
            'date' => $aside->date ?? \Carbon\Carbon::now()->toDateString(),
            'rising' => $aside->moon_rise ?? null,
            'setting' => $aside->moon_set ?? null,
            'illuminated_fraction' => $aside->moon_illuminated ?? null,
            'next_new_moon' => $aside->next_new_moon ?? null,
            '_ts' => \Carbon\Carbon::now()->toIso8601String(),
        ];
    } catch (\Throwable $_) {
        $payload = [
            'date' => \Carbon\Carbon::now()->toDateString(),
            'rising' => null,
            'setting' => null,
            'illuminated_fraction' => null,
            'next_new_moon' => null,
            '_ts' => \Carbon\Carbon::now()->toIso8601String(),
        ];
    }

    $session = (object) [
        'source_type_raw' => 'moon',
        'source_type' => 'Moon',
        'name' => 'Moon',
        'id' => 'moon',
        'slug' => 'moon',
    ];

    return view('object.moon-page', ['session' => $session, 'ephemerides' => $payload]);
})->name('object.show.moon')->middleware('doNotCacheResponse');

// Specific Sun path: serve a Livewire-first Sun page at /object/sun
Route::get('/object/sun', function () {
    try {
        $aside = new App\Http\Livewire\EphemerisAside();
        if (method_exists($aside, 'mount'))
            $aside->mount();
        $payload = [
            'date' => $aside->date ?? \Carbon\Carbon::now()->toDateString(),
            'sun_times' => $aside->sun_times ?? null,
            'nautical' => $aside->nautical ?? null,
            'astronomical' => $aside->astronomical ?? null,
            '_ts' => \Carbon\Carbon::now()->toIso8601String(),
        ];
    } catch (\Throwable $_) {
        $payload = [
            'date' => \Carbon\Carbon::now()->toDateString(),
            'sun_times' => null,
            'nautical' => null,
            'astronomical' => null,
            '_ts' => \Carbon\Carbon::now()->toIso8601String(),
        ];
    }

    $session = (object) [
        'source_type_raw' => 'sun',
        'source_type' => 'Sun',
        'name' => 'Sun',
        'id' => 'sun',
        'slug' => 'sun',
    ];

    // Compute unified modern observation counts (observations table, not legacy)
    $totalObservations = null;
    $drawings = null;
    $yourObservations = null;
    $yourDrawings = null;
    try {
        if (class_exists(\App\Models\Observation::class)) {
            $possibleKeys = [];
            if (!empty($session->slug))
                $possibleKeys[] = (string) $session->slug;
            if (!empty($session->name))
                $possibleKeys[] = (string) $session->name;
            if (!empty($session->id))
                $possibleKeys[] = (string) $session->id;
            $possibleKeys = array_values(array_unique(array_filter($possibleKeys)));

            if (!empty($possibleKeys)) {
                $lowerKeys = array_map(fn($k) => mb_strtolower((string) $k), $possibleKeys);
                $possibleKeys = array_values(array_unique(array_merge($possibleKeys, $lowerKeys)));

                // objects
                $unifiedObjectsCount = \App\Models\Observation::where('target_table', 'objects')
                    ->whereIn('target_key', $possibleKeys)
                    ->whereNull('legacy_source')
                    ->count();

                $unifiedObjectsDrawings = \App\Models\Observation::where('target_table', 'objects')
                    ->whereIn('target_key', $possibleKeys)
                    ->whereNull('legacy_source')
                    ->where('has_drawing', 1)
                    ->count();

                $totalObservations = (int) ($totalObservations ?? 0) + (int) $unifiedObjectsCount;
                $drawings = (int) ($drawings ?? 0) + (int) $unifiedObjectsDrawings;

                if (\Illuminate\Support\Facades\Auth::check()) {
                    $yourUnifiedObs = \App\Models\Observation::where('target_table', 'objects')
                        ->whereIn('target_key', $possibleKeys)
                        ->whereNull('legacy_source')
                        ->where('observer_user_id', \Illuminate\Support\Facades\Auth::user()->id)
                        ->count();

                    $yourUnifiedDraws = \App\Models\Observation::where('target_table', 'objects')
                        ->whereIn('target_key', $possibleKeys)
                        ->whereNull('legacy_source')
                        ->where('observer_user_id', \Illuminate\Support\Facades\Auth::user()->id)
                        ->where('has_drawing', 1)
                        ->count();

                    $yourObservations = (int) ($yourObservations ?? 0) + (int) $yourUnifiedObs;
                    $yourDrawings = (int) ($yourDrawings ?? 0) + (int) $yourUnifiedDraws;
                }

                // planets (Sun)
                $unifiedPlanetCount = \App\Models\Observation::where('target_table', 'planets')
                    ->whereIn('target_key', $possibleKeys)
                    ->whereNull('legacy_source')
                    ->count();

                $unifiedPlanetDrawings = \App\Models\Observation::where('target_table', 'planets')
                    ->whereIn('target_key', $possibleKeys)
                    ->whereNull('legacy_source')
                    ->where('has_drawing', 1)
                    ->count();

                $totalObservations = (int) ($totalObservations ?? 0) + (int) $unifiedPlanetCount;
                $drawings = (int) ($drawings ?? 0) + (int) $unifiedPlanetDrawings;

                if (\Illuminate\Support\Facades\Auth::check()) {
                    $yourUnifiedPlanetObs = \App\Models\Observation::where('target_table', 'planets')
                        ->whereIn('target_key', $possibleKeys)
                        ->whereNull('legacy_source')
                        ->where('observer_user_id', \Illuminate\Support\Facades\Auth::user()->id)
                        ->count();

                    $yourUnifiedPlanetDraws = \App\Models\Observation::where('target_table', 'planets')
                        ->whereIn('target_key', $possibleKeys)
                        ->whereNull('legacy_source')
                        ->where('observer_user_id', \Illuminate\Support\Facades\Auth::user()->id)
                        ->where('has_drawing', 1)
                        ->count();

                    $yourObservations = (int) ($yourObservations ?? 0) + (int) $yourUnifiedPlanetObs;
                    $yourDrawings = (int) ($yourDrawings ?? 0) + (int) $yourUnifiedPlanetDraws;
                }
            }
        }
    } catch (\Throwable $_) {
        // ignore
    }

    return view('object.sun-page', ['session' => $session, 'ephemerides' => $payload, 'totalObservations' => $totalObservations, 'drawings' => $drawings, 'yourObservations' => $yourObservations, 'yourDrawings' => $yourDrawings]);
})->name('object.show.sun')->middleware('doNotCacheResponse');

// Create object multi-step wizard (must be before /object/{slug} so 'create' isn't captured as a slug)
Route::get('/object/create', [App\Http\Controllers\ObjectController::class, 'create'])
    ->name('object.create')->middleware(['auth', 'doNotCacheResponse']);

// Add comet via create wizard: select from comets_orbital_elements and persist to cometobjects
Route::get('/object/create/comet', [App\Http\Controllers\ObjectController::class, 'createComet'])
    ->name('object.create.comet')->middleware(['auth', 'doNotCacheResponse']);

Route::post('/object/create/comet', [App\Http\Controllers\ObjectController::class, 'storeComet'])
    ->name('object.store.comet')->middleware(['auth', 'doNotCacheResponse']);

Route::post('/object/check-name', [App\Http\Controllers\ObjectController::class, 'checkName'])
    ->name('object.checkName')->middleware(['auth', 'doNotCacheResponse']);

Route::get('/object/name-search', [App\Http\Controllers\ObjectController::class, 'nameSearch'])
    ->name('object.nameSearch')->middleware(['auth', 'doNotCacheResponse']);

Route::get('/object/coords', [App\Http\Controllers\ObjectController::class, 'coordsForm'])
    ->name('object.coordsForm')->middleware(['auth', 'doNotCacheResponse']);

Route::post('/object/check-coords', [App\Http\Controllers\ObjectController::class, 'checkCoords'])
    ->name('object.checkCoords')->middleware(['auth', 'doNotCacheResponse']);

Route::get('/object/coords-search', [App\Http\Controllers\ObjectController::class, 'coordsSearch'])
    ->name('object.coordsSearch')->middleware(['auth', 'doNotCacheResponse']);

Route::get('/object/details', [App\Http\Controllers\ObjectController::class, 'detailsForm'])
    ->name('object.details')->middleware(['auth', 'doNotCacheResponse']);

Route::post('/object', [App\Http\Controllers\ObjectController::class, 'store'])
    ->name('object.store')->middleware(['auth', 'doNotCacheResponse']);

Route::get('/object/{slug}', [App\Http\Controllers\ObjectController::class, 'show'])
    ->name('object.show')->middleware('doNotCacheResponse');

// Search results page (Livewire-powered)
Route::get('/search', [App\Http\Controllers\SearchController::class, 'results'])
    ->name('search.results')->middleware('doNotCacheResponse');

// Advanced object search (filter builder form + results)
Route::get('/search/advanced', [App\Http\Controllers\SearchController::class, 'advanced'])
    ->name('search.advanced')->middleware('doNotCacheResponse');
Route::get('/search/advanced/results', [App\Http\Controllers\SearchController::class, 'advancedResults'])
    ->name('search.advanced.results')->middleware('doNotCacheResponse');
Route::get('/search/observations/advanced', [App\Http\Controllers\SearchController::class, 'advancedObservations'])
    ->name('search.observations.advanced')->middleware('doNotCacheResponse');
Route::get('/search/observations/advanced/results', [App\Http\Controllers\SearchController::class, 'advancedObservationResults'])
    ->name('search.observations.advanced.results')->middleware('doNotCacheResponse');

// Search exports (PDF / plain text / APD)
Route::get('/search/names.pdf', [App\Http\Controllers\SearchExportController::class, 'namesPdf'])
    ->name('search.names.pdf')->middleware('doNotCacheResponse');
Route::get('/search/table.pdf', [App\Http\Controllers\SearchExportController::class, 'tablePdf'])
    ->name('search.table.pdf')->middleware('doNotCacheResponse');
Route::get('/search/argo.txt', [App\Http\Controllers\SearchExportController::class, 'argoNavis'])
    ->name('search.argo')->middleware('doNotCacheResponse');
Route::get('/search/skylist.skylist', [App\Http\Controllers\SearchExportController::class, 'skylist'])
    ->name('search.skylist')->middleware('doNotCacheResponse');
Route::get('/search/stxt.txt', [App\Http\Controllers\SearchExportController::class, 'stxt'])
    ->name('search.stxt')->middleware('doNotCacheResponse');
Route::get('/search/apd.apd', [App\Http\Controllers\SearchExportController::class, 'apd'])
    ->name('search.apd')->middleware('doNotCacheResponse');

Route::get('/observing-list/{list}/export/names.pdf', [App\Http\Controllers\ObservingListExportController::class, 'namesPdf'])
    ->name('observing-list.export.names.pdf')->middleware(['auth', 'verified', 'doNotCacheResponse']);
Route::get('/observing-list/{list}/export/table.pdf', [App\Http\Controllers\ObservingListExportController::class, 'tablePdf'])
    ->name('observing-list.export.table.pdf')->middleware(['auth', 'verified', 'doNotCacheResponse']);
Route::get('/observing-list/{list}/export/argo.txt', [App\Http\Controllers\ObservingListExportController::class, 'argoNavis'])
    ->name('observing-list.export.argo')->middleware(['auth', 'verified', 'doNotCacheResponse']);
Route::get('/observing-list/{list}/export/skylist.skylist', [App\Http\Controllers\ObservingListExportController::class, 'skylist'])
    ->name('observing-list.export.skylist')->middleware(['auth', 'verified', 'doNotCacheResponse']);
Route::get('/observing-list/{list}/export/stxt.txt', [App\Http\Controllers\ObservingListExportController::class, 'stxt'])
    ->name('observing-list.export.stxt')->middleware(['auth', 'verified', 'doNotCacheResponse']);
Route::get('/observing-list/{list}/export/apd.apd', [App\Http\Controllers\ObservingListExportController::class, 'apd'])
    ->name('observing-list.export.apd')->middleware(['auth', 'verified', 'doNotCacheResponse']);

// Catalogs overview page
Route::get('/catalogs', [App\Http\Controllers\CatalogController::class, 'index'])
    ->name('catalogs.index');

// Edit object (only for admins and database experts)
Route::get('/object/{slug}/edit', [App\Http\Controllers\ObjectController::class, 'edit'])
    ->name('object.edit')->middleware(['auth', 'doNotCacheResponse']);

// Update object (only for admins and database experts)
Route::put('/object/{slug}', [App\Http\Controllers\ObjectController::class, 'update'])
    ->name('object.update')->middleware(['auth', 'doNotCacheResponse']);

// Update object from SIMBAD (only for admins and database experts)
Route::post('/object/{slug}/update-from-simbad', [App\Http\Controllers\ObjectController::class, 'updateFromSimbad'])
    ->name('object.updateFromSimbad')->middleware(['auth', 'doNotCacheResponse']);

// Delete object (only for admins and database experts)
Route::delete('/object/{slug}', [App\Http\Controllers\ObjectController::class, 'destroy'])
    ->name('object.destroy')->middleware(['auth', 'doNotCacheResponse']);

// Nearby names PDF export (uses query params ra, dec, radius)
Route::get('/object/{slug}/nearby-names.pdf', [App\Http\Controllers\NearbyExportController::class, 'namesPdf'])
    ->name('object.nearby.names.pdf')->middleware('doNotCacheResponse');

// Nearby table PDF export (landscape full table)
Route::get('/object/{slug}/nearby-table.pdf', [App\Http\Controllers\NearbyExportController::class, 'tablePdf'])
    ->name('object.nearby.table.pdf')->middleware('doNotCacheResponse');

// Nearby Argo Navis export (plain text)
Route::get('/object/{slug}/nearby-argo.txt', [App\Http\Controllers\NearbyExportController::class, 'argoNavis'])
    ->name('object.nearby.argo')->middleware('doNotCacheResponse');

// Nearby SkySafari .skylist export (plain text)
Route::get('/object/{slug}/nearby-skylist.skylist', [App\Http\Controllers\NearbyExportController::class, 'skylist'])
    ->name('object.nearby.skylist')->middleware('doNotCacheResponse');

// Nearby SkyTools plain TXT export (one object name per line)
Route::get('/object/{slug}/nearby-stxt.txt', [App\Http\Controllers\NearbyExportController::class, 'stxt'])
    ->name('object.nearby.stxt')->middleware('doNotCacheResponse');

// Nearby AstroPlanner .apd export (SQLite database file)
Route::get('/object/{slug}/nearby-apd.apd', [App\Http\Controllers\NearbyExportController::class, 'apd'])
    ->name('object.nearby.apd')->middleware('doNotCacheResponse');

// Create session (authenticated)
Route::get('/sessions/create', [App\Http\Controllers\SessionController::class, 'create'])
    ->name('session.create')->middleware(['auth', 'doNotCacheResponse']);
Route::post('/sessions', [App\Http\Controllers\SessionController::class, 'store'])
    ->name('session.store')->middleware(['auth', 'doNotCacheResponse']);

// My sessions (authenticated)
// Note: /my-sessions removed in favor of /sessions/{user} - use route('session.user', [Auth::user()->slug]) when needed

// All sessions (public)
Route::get('/sessions', [App\Http\Controllers\SessionController::class, 'all'])->name('session.all');

// Sessions by user (public): show sessions for a given observer username or slug
Route::get('/sessions/{user}', [App\Http\Controllers\SessionController::class, 'user'])
    ->name('session.user')->middleware('doNotCacheResponse');

// Delete session (authenticated, owner only - controller enforces)
Route::post('/sessions/{session}/delete', [App\Http\Controllers\SessionController::class, 'destroy'])
    ->name('session.destroy')->middleware('auth');

// Adapt session: create a new session prefilled from an existing one (owner only)
Route::get('/sessions/{session}/adapt', [App\Http\Controllers\SessionController::class, 'adapt'])
    ->name('session.adapt')->middleware(['auth', 'doNotCacheResponse']);

Route::get('/instrumentset/{user}/{instrumentset}/edit', 'App\Http\Controllers\InstrumentSetController@edit')
    ->name('instrumentset.edit')->middleware('auth');

Route::get('/admin/instrumentset', 'App\Http\Controllers\InstrumentSetController@indexAdmin')
    ->name('instrumentset.indexAdmin')->can('add_sketch', User::class);

// Likes for observations (deepsky / comet)
Route::post('/observation/like', [App\Http\Controllers\ObservationLikeController::class, 'toggle'])->name('observation.like')->middleware('auth');

// Comments for observations (deepsky / comet)
Route::post('/observation/comments', [App\Http\Controllers\ObservationCommentController::class, 'store'])
    ->name('observation.comments.store')
    ->middleware(['auth', 'verified']);
Route::delete('/observation/comments/{comment}', [App\Http\Controllers\ObservationCommentController::class, 'destroy'])
    ->name('observation.comments.destroy')
    ->middleware(['auth', 'verified']);

// Popular observations
Route::get('/popular-observations', [App\Http\Controllers\PopularObservationController::class, 'index'])->name('observations.popular');

Route::get('/popular-objects', [App\Http\Controllers\PopularObjectController::class, 'index'])->name('objects.popular');

// Popular sessions
Route::get('/popular-sessions', function () {
    return view('observations.popular-sessions');
})->name('observations.popular.sessions');

// Messages
Route::get('/messages', [App\Http\Controllers\MessagesController::class, 'index'])
    ->name('messages.index')
    ->middleware(['auth', 'doNotCacheResponse']);
Route::get('/messages/create', [App\Http\Controllers\MessagesController::class, 'create'])
    ->name('messages.create')
    ->middleware(['auth', 'doNotCacheResponse']);
Route::post('/messages', [App\Http\Controllers\MessagesController::class, 'store'])
    ->name('messages.store')
    ->middleware('auth');
Route::get('/messages/{id}', [App\Http\Controllers\MessagesController::class, 'show'])
    ->name('messages.show')
    ->middleware(['auth', 'doNotCacheResponse']);

// Admin broadcast
Route::post('/messages/broadcast', [App\Http\Controllers\MessagesController::class, 'broadcast'])->name('messages.broadcast')->middleware('can:add_sketch,App\\Models\\User');

// Mark all messages as read
Route::post('/messages/mark-all-read', [App\Http\Controllers\MessagesController::class, 'markAllRead'])
    ->name('messages.markAllRead')
    ->middleware(['auth', 'doNotCacheResponse']);

// Delete all messages for current user (mark as deleted)
Route::post('/messages/delete-all', [App\Http\Controllers\MessagesController::class, 'deleteAll'])
    ->name('messages.deleteAll')
    ->middleware(['auth', 'doNotCacheResponse']);

// Reply data (plain-text message) for prefill via AJAX
Route::get('/messages/{id}/reply-data', [App\Http\Controllers\MessagesController::class, 'replyData'])
    ->name('messages.replyData')
    ->middleware(['auth', 'doNotCacheResponse']);

// Delete a message (mark deleted in legacy messagesDeleted table)
Route::post('/messages/{id}/delete', [App\Http\Controllers\MessagesController::class, 'destroy'])
    ->name('messages.destroy')
    ->middleware(['auth', 'doNotCacheResponse']);
// Bulk-delete grouped sent message (delete all per-recipient rows matching content of the representative id)
Route::post('/messages/{id}/delete-group', [App\Http\Controllers\MessagesController::class, 'bulkDestroy'])
    ->name('messages.destroyGroup')
    ->middleware(['auth', 'doNotCacheResponse']);

// Sitemap (cached): generates a simple sitemap.xml with main pages and recent public sessions
// Observing Lists
// Public discover page (no auth required)
Route::get('/observing-lists/discover', [App\Http\Controllers\ObservingListController::class, 'discover'])
    ->name('observing-lists.discover');

// Public list detail page (private lists still guarded in controller)
Route::get('/observing-list/{list}', [App\Http\Controllers\ObservingListController::class, 'show'])
    ->name('observing-list.show');

Route::middleware(['auth', 'verified'])->group(function () {
    // My observing lists (owned + subscribed)
    Route::get('/observing-lists', [App\Http\Controllers\ObservingListController::class, 'index'])
        ->name('observing-lists.index');

    // Set as active observing list
    Route::post('/observing-list/{list}/set-active', [App\Http\Controllers\ObservingListController::class, 'setActive'])
        ->name('observing-list.set-active');

    // Subscribe to public list
    Route::post('/observing-list/{list}/subscribe', [App\Http\Controllers\ObservingListController::class, 'subscribe'])
        ->name('observing-list.subscribe');

    // Unsubscribe from list
    Route::post('/observing-list/{list}/unsubscribe', [App\Http\Controllers\ObservingListController::class, 'unsubscribe'])
        ->name('observing-list.unsubscribe');

    // Toggle like on observing list (AJAX)
    Route::post('/observing-list/{list}/toggle-like', [App\Http\Controllers\ObservingListController::class, 'toggleLike'])
        ->name('observing-list.toggle-like');

    // Create new observing list
    Route::get('/observing-lists/create', [App\Http\Controllers\ObservingListController::class, 'create'])
        ->name('observing-list.create');
    Route::post('/observing-lists', [App\Http\Controllers\ObservingListController::class, 'store'])
        ->name('observing-list.store');

    // Edit / update / delete an observing list
    Route::get('/observing-list/{list}/edit', [App\Http\Controllers\ObservingListController::class, 'edit'])
        ->name('observing-list.edit');
    Route::put('/observing-list/{list}', [App\Http\Controllers\ObservingListController::class, 'update'])
        ->name('observing-list.update');
    Route::delete('/observing-list/{list}', [App\Http\Controllers\ObservingListController::class, 'destroy'])
        ->name('observing-list.destroy');

    // Items: add (from object page, quick-add via Livewire component) and remove
    Route::post('/observing-list/{list}/items', [App\Http\Controllers\ObservingListController::class, 'addItem'])
        ->name('observing-list.items.store');
    Route::get('/observing-list/{list}/items/{item}/edit', [App\Http\Controllers\ObservingListController::class, 'editItem'])
        ->name('observing-list.items.edit');
    Route::patch('/observing-list/{list}/items/{item}', [App\Http\Controllers\ObservingListController::class, 'updateItem'])
        ->name('observing-list.items.update');
    Route::delete('/observing-list/{list}/items/{item}', [App\Http\Controllers\ObservingListController::class, 'removeItem'])
        ->name('observing-list.items.destroy');

    // Autofill notes for all un-noted items in a list from legacy observations
    Route::post('/observing-list/{list}/items/autofill-notes', [App\Http\Controllers\ObservingListController::class, 'batchAutofillNotes'])
        ->name('observing-list.items.autofill-notes');

    // Empty all items from a list (but keep the list itself)
    Route::post('/observing-list/{list}/empty', [App\Http\Controllers\ObservingListController::class, 'emptyList'])
        ->name('observing-list.empty');

    // Import objects from a file
    Route::post('/observing-list/{list}/import', [App\Http\Controllers\ObservingListController::class, 'importFromFile'])
        ->name('observing-list.import');

    // Batch-add objects to the user's active observing list
    Route::post('/observing-lists/active/batch-add', [App\Http\Controllers\ObservingListController::class, 'batchAddToActiveList'])
        ->name('observing-list.active.batch-add');

    // Toggle one object in the user's active observing list
    Route::post('/observing-lists/active/toggle-item', [App\Http\Controllers\ObservingListController::class, 'toggleActiveListItem'])
        ->name('observing-list.active.toggle-item');

    // Batch-add nearby objects (by coordinates) to the user's active observing list
    Route::post('/observing-lists/active/batch-add-nearby', [App\Http\Controllers\ObservingListController::class, 'batchAddNearbyToActiveList'])
        ->name('observing-list.active.batch-add-nearby');

    // Comments: add and delete
    Route::post('/observing-list/{list}/comments', [App\Http\Controllers\ObservingListController::class, 'storeComment'])
        ->name('observing-list.comments.store');
    Route::delete('/observing-list/{list}/comments/{comment}', [App\Http\Controllers\ObservingListController::class, 'destroyComment'])
        ->name('observing-list.comments.destroy');
});

Route::get('/sitemap.xml', function () {
    $xml = Cache::remember('sitemap_xml_v1', 60 * 60 * 12, function () {
        $base = rtrim(config('app.url') ?: url('/'), '/');
        $now = now()->toAtomString();

        $urls = [];
        // Static / important pages
        $urls[] = ['loc' => $base . '/', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => $base . '/sessions', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.8'];
        $urls[] = ['loc' => $base . '/popular-sessions', 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.7'];
        $urls[] = ['loc' => $base . '/popular-objects', 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.7'];
        $urls[] = ['loc' => $base . '/popular-observations', 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.7'];
        $urls[] = ['loc' => $base . '/drawings', 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.5'];
        $urls[] = ['loc' => $base . '/cometdrawings', 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.5'];

        // Recent public sessions (limit to avoid heavy queries)
        try {
            $sessions = ObservationSession::where('active', 1)
                ->orderByDesc('enddate')
                ->limit(1000)
                ->get(['slug', 'observerid', 'updated_at']);

            foreach ($sessions as $s) {
                $user = User::where('username', $s->observerid)->first();
                $slug = $user ? $user->slug : $s->observerid;
                $loc = $base . '/session/' . ($slug ?? $s->observerid) . '/' . ($s->slug ?? $s->id);
                $urls[] = ['loc' => $loc, 'lastmod' => optional($s->updated_at)->toAtomString() ?? $now, 'changefreq' => 'monthly', 'priority' => '0.5'];
            }
        } catch (\Throwable $e) {
            // If the DB is unavailable, return only the static urls
        }

        // Build XML (use PHP_EOL so we don't include literal "\n" sequences
        // and ensure proper XML escaping for values)
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        foreach ($urls as $u) {
            $xml .= "  <url>" . PHP_EOL;
            // Use XML-safe escaping
            $loc = htmlspecialchars($u['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $xml .= '    <loc>' . $loc . "</loc>" . PHP_EOL;
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>" . PHP_EOL;
            }
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>" . PHP_EOL;
            $xml .= '    <priority>' . $u['priority'] . "</priority>" . PHP_EOL;
            $xml .= "  </url>" . PHP_EOL;
        }
        $xml .= '</urlset>' . PHP_EOL;

        return $xml;
    });

    return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');
