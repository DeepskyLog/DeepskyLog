<?php

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

Route::get('/', 'PagesController@home');

Route::get('/privacy', 'PagesController@privacy');

Route::post('/lang', 'LanguageController@changeLang');

Route::post('/setSession', 'SessionController@createSession');

// Lenses
Route::get('/lens/autocomplete', 'LensController@dataAjax')->name('lens.dataAjax');

Route::get('/lens/create/{lens}', 'LensController@create')->middleware('verified')
    ->name('lens.create');

Route::get('/lens/admin', 'LensController@indexAdmin')->name('lens.indexAdmin');

Route::resource(
    'lens',
    'LensController',
    ['parameters' => ['lens' => 'lens']]
)->middleware('verified')->except('show');

Route::get('/lens/{lens}', 'LensController@show')->name('lens.show');

Route::get('/getLensJson/{id}', 'LensController@getLensJson');

Route::get('/lens/{lens}/getImage', 'LensController@getImage')
    ->name('lens.getImage');

Route::post('/lens/{lens}/deleteImage', 'LensController@deleteImage')
    ->name('lens.deleteImage');

// Filters
Route::get('/filter/autocomplete', 'FilterController@dataAjax')
    ->name('filter.dataAjax');

Route::get('/filter/create/{filter}', 'FilterController@create')
    ->middleware('verified')
    ->name('filter.create');

Route::get('/filter/admin', 'FilterController@indexAdmin')
    ->name('filter.indexAdmin');

Route::resource(
    'filter',
    'FilterController',
    ['parameters' => ['filter' => 'filter']]
)->middleware('verified')->except('show');

Route::get('/filter/{filter}', 'FilterController@show')->name('filter.show');

Route::get('/getFilterJson/{id}', 'FilterController@getFilterJson');

Route::get('/filter/{filter}/getImage', 'FilterController@getImage')
    ->name('filter.getImage');

Route::post('/filter/{filter}/deleteImage', 'FilterController@deleteImage')
    ->name('filter.deleteImage');

// Eyepieces
Route::get('/eyepiece/autocomplete', 'EyepieceController@dataAjax')
    ->name('eyepiece.dataAjax');

Route::get('/eyepiece/create/{eyepiece}', 'EyepieceController@create')
    ->middleware('verified')
    ->name('eyepiece.create');

Route::get('/eyepiece/admin', 'EyepieceController@indexAdmin')
    ->name('eyepiece.indexAdmin');

Route::resource(
    'eyepiece',
    'EyepieceController',
    ['parameters' => ['eyepiece' => 'eyepiece']]
)->middleware('verified')->except('show');

Route::get('/eyepiece/{eyepiece}', 'EyepieceController@show')->name('eyepiece.show');

Route::get('/getEyepieceJson/{id}', 'EyepieceController@getEyepieceJson');

Route::get('/getEyepieceTypeJson/{brand}', 'EyepieceController@getEyepieceTypeJson');

Route::get('/eyepiece/{eyepiece}/getImage', 'EyepieceController@getImage')
    ->name('eyepiece.getImage');

Route::post('/eyepiece/{eyepiece}/deleteImage', 'EyepieceController@deleteImage')
    ->name('eyepiece.deleteImage');

// Instruments
Route::get('/instrument/autocomplete', 'InstrumentController@dataAjax')
    ->name('instrument.dataAjax');

Route::get('/instrument/create/{instrument}', 'InstrumentController@create')
    ->middleware('verified')
    ->name('instrument.create');

Route::get('/instrument/admin', 'InstrumentController@indexAdmin')
    ->name('instrument.indexAdmin');

Route::resource(
    'instrument',
    'InstrumentController',
    ['parameters' => ['instrument' => 'instrument']]
)->middleware('verified')->except('show');

Route::get('/instrument/{instrument}', 'InstrumentController@show')
    ->name('instrument.show');

Route::get('/getInstrumentJson/{id}', 'InstrumentController@getInstrumentJson');

Route::get('/instrument/{instrument}/getImage', 'InstrumentController@getImage')
    ->name('instrument.getImage');

Route::post('/instrument/{instrument}/deleteImage', 'InstrumentController@deleteImage')
    ->name('instrument.deleteImage');

// Locations
Route::get('/location/autocomplete', 'LocationController@dataAjax')
    ->name('location.dataAjax');

Route::get('/location/create/{location}', 'LocationController@create')
    ->middleware('verified')
    ->name('location.create');

Route::get('/location/admin', 'LocationController@indexAdmin')
    ->name('location.indexAdmin');

Route::get('/location/lightpollutionmap', 'LocationController@lightpollutionmap');

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

Route::get('/getLocationJson/{id}', 'LocationController@getLocationJson');

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
Route::group(['prefix' => 'messages'], function () {
    Route::get('/', ['as' => 'messages', 'uses' => 'MessagesController@index']);
    Route::get('create', ['as' => 'messages.create', 'uses' => 'MessagesController@create']);
    Route::get('createAll', 'MessagesController@createAll')->name('messages.createAll');
    Route::post('/', ['as' => 'messages.store', 'uses' => 'MessagesController@store']);
    Route::get('{id}', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
    Route::put('{id}', ['as' => 'messages.update', 'uses' => 'MessagesController@update']);
    Route::get('/create/{id}', 'MessagesController@createId')->name('messages.createId');
});

// Targets
Route::get('/catalogs', 'TargetController@catalogs')
    ->name('catalogs');

Route::get('/getCatalogData/{catalog}', 'TargetController@getCatalogData');
Route::get('/getConstellationInfo/{catalog}', 'TargetController@getConstellationInfo');
Route::get('/getTypeInfo/{catalog}', 'TargetController@getTypeInfo');
Route::get('/target/{name}', 'TargetController@show');

// Social log in
Route::get('/redirect/{service}', 'SocialAuthController@redirect');
//Route::get('/callback/{service}', 'SocialAuthController@callback');
Route::get('/callback/{service}', 'Auth\LoginController@handleProviderCallback');
