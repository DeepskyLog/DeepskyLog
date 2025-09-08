<?php

use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Models\ObservationSession;
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
    Route::get('/dashboard', function () {
        return view('welcome');
    })->name('dashboard');
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
Route::get('/observers/{observer}', 'App\Http\Controllers\ObserverController@show')->name('observer.show');

// Drawings
Route::get('/drawings', 'App\Http\Controllers\DrawingController@index')->name('drawings.index');
Route::get('/cometdrawings', 'App\Http\Controllers\CometDrawingController@index')->name('cometdrawings.index');
Route::get('/drawings/{observer}', 'App\Http\Controllers\DrawingController@show')->name('drawings.show');
Route::get('/cometdrawings/{observer}', 'App\Http\Controllers\CometDrawingController@show')->name('cometdrawings.show');

// Sketch of the week / month
Route::get('/sketch-of-the-week', function () {
    return view('sketch-of-the-week-month',
        ['sketches' => SketchOfTheWeek::orderBy('date', 'desc')->paginate(20),
            'week_month' => __('Week')]);
})->name('sketch-of-the-week');
Route::get('/sketch-of-the-month', function () {
    return view('sketch-of-the-week-month',
        ['sketches' => SketchOfTheMonth::orderBy('date', 'desc')->paginate(20),
            'week_month' => __('Month')]);
})->name('sketch-of-the-month');
Route::get('/sketch-of-the-week/create', 'App\Http\Controllers\SketchOfTheWeekController@create')->name('sketch-of-the-week.create')->can('add_sketch', User::class);
Route::post('/sketch-of-the-week', 'App\Http\Controllers\SketchOfTheWeekController@store')->name('sketch-of-the-week.store')->can('add_sketch', User::class);
Route::get('/sketch-of-the-month/create', 'App\Http\Controllers\SketchOfTheMonthController@create')->name('sketch-of-the-month.create')->can('add_sketch', User::class);
Route::post('/sketch-of-the-month', 'App\Http\Controllers\SketchOfTheMonthController@store')->name('sketch-of-the-month.store')->can('add_sketch', User::class);

Route::get('/', [App\Http\Controllers\SessionController::class, 'homepage']);
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/sponsors', 'layouts.sponsors');
Route::view('/downloads/magazines', 'layouts.downloads.magazines');
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
    ->name('session.show');

// Create session (authenticated)
Route::get('/sessions/create', [App\Http\Controllers\SessionController::class, 'create'])->name('session.create')->middleware('auth');
Route::post('/sessions', [App\Http\Controllers\SessionController::class, 'store'])->name('session.store')->middleware('auth');

// My sessions (authenticated)
Route::get('/my-sessions', [App\Http\Controllers\SessionController::class, 'mine'])->name('session.mine')->middleware('auth');

// All sessions (public)
Route::get('/sessions', [App\Http\Controllers\SessionController::class, 'all'])->name('session.all');

Route::get('/instrumentset/{user}/{instrumentset}/edit', 'App\Http\Controllers\InstrumentSetController@edit')
    ->name('instrumentset.edit')->middleware('auth');

Route::get('/admin/instrumentset', 'App\Http\Controllers\InstrumentSetController@indexAdmin')
    ->name('instrumentset.indexAdmin')->can('add_sketch', User::class);

// Likes for observations (deepsky / comet)
Route::post('/observation/like', [App\Http\Controllers\ObservationLikeController::class, 'toggle'])->name('observation.like')->middleware('auth');

// Popular observations
Route::get('/popular-observations', [App\Http\Controllers\PopularObservationController::class, 'index'])->name('observations.popular');

// Popular sessions
Route::get('/popular-sessions', function () {
    return view('observations.popular-sessions');
})->name('observations.popular.sessions');

// Messages
Route::get('/messages', [App\Http\Controllers\MessagesController::class, 'index'])->name('messages.index')->middleware('auth');
Route::get('/messages/create', [App\Http\Controllers\MessagesController::class, 'create'])->name('messages.create')->middleware('auth');
Route::post('/messages', [App\Http\Controllers\MessagesController::class, 'store'])->name('messages.store')->middleware('auth');
Route::get('/messages/{id}', [App\Http\Controllers\MessagesController::class, 'show'])->name('messages.show')->middleware('auth');

// Admin broadcast
Route::post('/messages/broadcast', [App\Http\Controllers\MessagesController::class, 'broadcast'])->name('messages.broadcast')->middleware('can:add_sketch,App\\Models\\User');

// Mark all messages as read
Route::post('/messages/mark-all-read', [App\Http\Controllers\MessagesController::class, 'markAllRead'])->name('messages.markAllRead')->middleware('auth');

// Reply data (plain-text message) for prefill via AJAX
Route::get('/messages/{id}/reply-data', [App\Http\Controllers\MessagesController::class, 'replyData'])->name('messages.replyData')->middleware('auth');

// Delete a message (mark deleted in legacy messagesDeleted table)
Route::post('/messages/{id}/delete', [App\Http\Controllers\MessagesController::class, 'destroy'])->name('messages.destroy')->middleware('auth');

// Sitemap (cached): generates a simple sitemap.xml with main pages and recent public sessions
Route::get('/sitemap.xml', function () {
    $xml = Cache::remember('sitemap_xml_v1', 60 * 60 * 12, function () {
        $base = rtrim(config('app.url') ?: url('/'), '/');
        $now = now()->toAtomString();

        $urls = [];
        // Static / important pages
        $urls[] = ['loc' => $base.'/', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => $base.'/sessions', 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.8'];
        $urls[] = ['loc' => $base.'/popular-sessions', 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.7'];
        $urls[] = ['loc' => $base.'/popular-observations', 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.7'];
        $urls[] = ['loc' => $base.'/drawings', 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.5'];
        $urls[] = ['loc' => $base.'/cometdrawings', 'lastmod' => $now, 'changefreq' => 'monthly', 'priority' => '0.5'];

        // Recent public sessions (limit to avoid heavy queries)
        try {
            $sessions = ObservationSession::where('active', 1)
                ->orderByDesc('enddate')
                ->limit(1000)
                ->get(['slug', 'observerid', 'updated_at']);

            foreach ($sessions as $s) {
                $user = User::where('username', $s->observerid)->first();
                $slug = $user ? $user->slug : $s->observerid;
                $loc = $base.'/session/'.($slug ?? $s->observerid).'/'.($s->slug ?? $s->id);
                $urls[] = ['loc' => $loc, 'lastmod' => optional($s->updated_at)->toAtomString() ?? $now, 'changefreq' => 'monthly', 'priority' => '0.5'];
            }
        } catch (\Throwable $e) {
            // If the DB is unavailable, return only the static urls
        }

        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>\n';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n';
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($u['loc'])."</loc>\n";
            if (! empty($u['lastmod'])) {
                $xml .= '    <lastmod>'.$u['lastmod']."</lastmod>\n";
            }
            $xml .= '    <changefreq>'.$u['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$u['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $xml;
    });

    return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');
