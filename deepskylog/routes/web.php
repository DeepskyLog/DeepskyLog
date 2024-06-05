<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

// Teams
Route::get('/teams/{team}', 'App\Http\Controllers\DeepskyLogTeamController@show')->name('teams.show');

// Observers
Route::get('/observers/{observer}', 'App\Http\Controllers\ObserverController@show')->name('observer.show');

Route::get('/sponsors', function () {
    return view('layouts.sponsors');
});
