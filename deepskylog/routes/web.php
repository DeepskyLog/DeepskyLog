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
