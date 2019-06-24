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

Auth::routes(['verify' => true]);

Route::post('/users/upload', 'UserController@upload')->name('users.upload');

Route::delete('/users/upload', 'UserController@delete')->name('users.delete');

Route::get('/users/{user}/getImage', 'UserController@getImage')
    ->name('users.getImage');

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

Route::group(['prefix' => 'messages'], function () {
    Route::get('/', ['as' => 'messages', 'uses' => 'MessagesController@index']);
    Route::get('create', ['as' => 'messages.create', 'uses' => 'MessagesController@create']);
    Route::get('createAll', 'MessagesController@createAll')->name('messages.createAll');
    Route::post('/', ['as' => 'messages.store', 'uses' => 'MessagesController@store']);
    Route::get('{id}', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
    Route::put('{id}', ['as' => 'messages.update', 'uses' => 'MessagesController@update']);
    Route::get('/create/{id}', 'MessagesController@createId')->name('messages.createId');
});
