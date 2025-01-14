<?php

use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
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

Route::view('/', 'welcome');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/sponsors', 'layouts.sponsors');
Route::view('/downloads/magazines', 'layouts.downloads.magazines');

// Instruments
// Route::get('/instrument/create/{instrument}', 'App\Http\Controllers\InstrumentController@create')
//    ->middleware('verified');
//
// Route::get('/instrument/admin', 'App\Http\Controllers\InstrumentController@indexAdmin')
//    ->name('instrument.indexAdmin');
//
// Route::resource(
//    'instrument',
//    'App\Http\Controllers\InstrumentController',
//    ['parameters' => ['instrument' => 'instrument']]
// )->middleware('verified')->except('show');

Route::get('/instrument/{instrument}', 'App\Http\Controllers\InstrumentController@show')
    ->name('instrument.show');

// Route::get('/instrument/{instrument}/getImage', 'App\Http\Controllers\InstrumentController@getImage')
//    ->name('instrument.getImage');

// Route::post('/instrument/{instrument}/deleteImage', 'App\Http\Controllers\InstrumentController@deleteImage')
//    ->name('instrument.deleteImage');

// Route::get('/getinstruments/{id}', 'App\Http\Controllers\InstrumentController@getinstrumentsAjax');
