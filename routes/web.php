<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PageController@home');
Route::get('/home', 'PageController@home');

Route::get('/privacy', 'PageController@privacy');

Route::post('/lang', 'LanguageController@changeLang');

Route::post('/setSession', 'SessionController@createSession');

// Needed for medialibrary-pro
Route::mediaLibrary();

// Lenses
Route::get('/lens/create/{lens}', 'LensController@create')->middleware('verified');

Route::get('/lens/admin', 'LensController@indexAdmin')->name('lens.indexAdmin');

Route::resource(
    'lens',
    'LensController',
    ['parameters' => ['lens' => 'lens']]
)->middleware('verified')->except('show');

Route::get('/lens/{lens}', 'LensController@show')->name('lens.show');

Route::get('/lens/{lens}/getImage', 'LensController@getImage')
    ->name('lens.getImage');

Route::post('/lens/{lens}/deleteImage', 'LensController@deleteImage')
    ->name('lens.deleteImage');

Route::get('/getlenses/{id}', 'LensController@getlensesAjax');

// Filters
Route::get('/filter/create/{filter}', 'FilterController@create')
    ->middleware('verified');

Route::get('/filter/admin', 'FilterController@indexAdmin')
    ->name('filter.indexAdmin');

Route::resource(
    'filter',
    'FilterController',
    ['parameters' => ['filter' => 'filter']]
)->middleware('verified')->except('show');

Route::get('/filter/{filter}', 'FilterController@show')->name('filter.show');

Route::get('/filter/{filter}/getImage', 'FilterController@getImage')
    ->name('filter.getImage');

Route::post('/filter/{filter}/deleteImage', 'FilterController@deleteImage')
    ->name('filter.deleteImage');

// Eyepieces
Route::get('/eyepiece/create/{eyepiece}', 'EyepieceController@create')
    ->middleware('verified');

Route::get('/eyepiece/admin', 'EyepieceController@indexAdmin')
    ->name('eyepiece.indexAdmin');

Route::resource(
    'eyepiece',
    'EyepieceController',
    ['parameters' => ['eyepiece' => 'eyepiece']]
)->middleware('verified')->except('show');

Route::get('/eyepiece/{eyepiece}', 'EyepieceController@show')->name('eyepiece.show');

Route::get('/eyepiece/{eyepiece}/getImage', 'EyepieceController@getImage')
    ->name('eyepiece.getImage');

Route::post('/eyepiece/{eyepiece}/deleteImage', 'EyepieceController@deleteImage')
    ->name('eyepiece.deleteImage');

// Instruments
Route::get('/instrument/create/{instrument}', 'InstrumentController@create')
    ->middleware('verified');

Route::get('/instrument/admin', 'InstrumentController@indexAdmin')
    ->name('instrument.indexAdmin');

Route::resource(
    'instrument',
    'InstrumentController',
    ['parameters' => ['instrument' => 'instrument']]
)->middleware('verified')->except('show');

Route::get('/instrument/{instrument}', 'InstrumentController@show')
    ->name('instrument.show');

Route::get('/instrument/{instrument}/getImage', 'InstrumentController@getImage')
    ->name('instrument.getImage');

Route::post('/instrument/{instrument}/deleteImage', 'InstrumentController@deleteImage')
    ->name('instrument.deleteImage');

Route::get('/getinstruments/{id}', 'InstrumentController@getinstrumentsAjax');

// Locations
Route::get('/location/create/{location}', 'LocationController@create')
    ->middleware('verified');

Route::get('/location/admin', 'LocationController@indexAdmin')
    ->name('location.indexAdmin');

Route::get('/location/{location}/getImage', 'LocationController@getImage')
    ->name('location.getImage');

Route::post('/location/{location}/deleteImage', 'LocationController@deleteImage')
    ->name('location.deleteImage');

Route::resource(
    'location',
    'LocationController',
    ['parameters' => ['location' => 'location']]
)->middleware('verified')->except('show');

Route::get('/location/{location}', 'LocationController@show')
    ->name('location.show');

// Users
Auth::routes(['verify' => true]);

Route::get('/users/{user}/getImage', 'UserController@getImage')
    ->name('users.getImage');

Route::post('/users/{user}/deleteImage', 'UserController@deleteImage')
    ->name('users.deleteImage');

Route::get(
    '/users/getAuthenticatedUserImage/',
    'UserController@getAuthenticatedUserImage'
)->name('users.getAuthenticatedUserImage');

Route::resource('users', 'UserController')->middleware('isAdmin')->only(
    ['index', 'update', 'destroy', 'edit']
);

Route::resource('users', 'UserController')->except(
    ['index', 'update', 'destroy', 'edit']
);

Route::patch('/users/{user}/settings', 'UserController@patchSettings')
    ->name('users.patchSettings');

Route::get('/users/{user}/settings', 'UserController@settings')
    ->name('users.settings');

// Messages
Route::group(
    ['prefix' => 'messages'],
    function () {
        Route::get('/', ['as' => 'messages', 'uses' => 'MessageController@index']);
        Route::get(
            'create',
            ['as' => 'messages.create', 'uses' => 'MessageController@create']
        );
        Route::get(
            'markAllRead',
            ['as' => 'messages.markAlRead', 'uses' => 'MessageController@markAllRead']
        );
        Route::get(
            'createAll',
            'MessageController@createAll'
        )->name('messages.createAll');
        Route::post(
            '/',
            ['as' => 'messages.store', 'uses' => 'MessageController@store']
        );
        Route::get(
            '{id}',
            ['as' => 'messages.show', 'uses' => 'MessageController@show']
        );
        Route::put(
            '{id}',
            ['as' => 'messages.update', 'uses' => 'MessageController@update']
        );
        Route::get(
            '/create/{id}',
            'MessageController@createId'
        )->name('messages.createId');
    }
);

// Targets
Route::get('/catalogs', 'TargetController@catalogs')
    ->name('catalogs');

// Also allow slashes in URIs for comets
Route::get('/target/{name}', 'TargetController@show')
    ->where('name', '[ A-Za-z0-9_/-]+');

Route::get('/target/autocomplete', 'TargetController@dataAjax')
    ->name('target.dataAjax');

Route::get('/target', 'TargetController@index')->name('target.search');

Route::post('/target', 'TargetController@search');

Route::get('/sponsors', function () {
    return view('layout.sponsors');
});

Route::get('/downloads/imageCatalogs', function () {
    return view('layout.imageCatalogs');
});

Route::get('/downloads/forms', function () {
    return view('layout.forms');
});

// Sets
Route::get('/set/admin', 'SetController@indexAdmin')
    ->name('set.indexAdmin');

Route::resource(
    'set',
    'SetController',
    ['parameters' => ['set' => 'set']]
)->middleware('verified');

Route::get('/set/{set}', 'SetController@show')->name('set.show');

// Observation lists
Route::get('/observationList/create/{list}', 'ObservationListController@create')
    ->middleware('verified');

Route::get('/observationList/admin', 'ObservationListController@indexAdmin')
    ->name('observationList.indexAdmin');

Route::resource(
    'observationList',
    'ObservationListController',
    ['parameters' => ['observationList' => 'observationList']]
)->middleware('verified')->except('show');

Route::get('/observationList/{list}', 'ObservationListController@show')
    ->name('observationList.show');

// Social log in
Route::get('/login/github', [LoginController::class, 'redirectToGithub']);
Route::get('/login/github/callback', [LoginController::class, 'handleGithubCallback']);
Route::get('/login/google', [LoginController::class, 'redirectToGoogle']);
Route::get('/login/google/callback', [LoginController::class, 'handleGoogleCallback']);
Route::get('/login/facebook', [LoginController::class, 'redirectToFacebook']);
Route::get('/login/facebook/callback', [LoginController::class, 'handleFacebookCallback']);
Route::get('/login/twitter', [LoginController::class, 'redirectToTwitter']);
Route::get('/login/twitter/callback', [LoginController::class, 'handleTwitterCallback']);
