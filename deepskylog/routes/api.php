<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
            'name' => \Countries::getOne(auth()->user()->country, 'en'),
        ];
    }
    foreach (\Countries::getList('en') as $code => $name) {
        if ($request->search == '' || Str::contains(Str::lower($name), Str::lower($request->search))) {
            $allCountries[] = [
                'id' => $code,
                'name' => $name,
            ];
        }
    }

    return $allCountries;
})->name('countries.index');
