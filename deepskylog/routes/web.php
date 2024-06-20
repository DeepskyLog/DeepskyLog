<?php

use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
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
        return view('dashboard');
    })->name('dashboard');
});

// Teams
Route::get('/teams/{team}', 'App\Http\Controllers\DeepskyLogTeamController@show')->name('teams.show');

// Observers
Route::get('/observers/{observer}', 'App\Http\Controllers\ObserverController@show')->name('observer.show');

// Sketch of the week / month
Route::view('/sketch-of-the-week', 'sketch-of-the-week-month', ['sketches' => SketchOfTheWeek::paginate(20), 'week_month' => __('Week')])->name('sketch-of-the-week');
Route::view('/sketch-of-the-month', 'sketch-of-the-week-month', ['sketches' => SketchOfTheMonth::paginate(20), 'week_month' => __('Month')])->name('sketch-of-the-month');

Route::view('/', 'welcome');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/sponsors', 'layouts.sponsors');
Route::view('/downloads/magazines', 'layouts.downloads.magazines');
