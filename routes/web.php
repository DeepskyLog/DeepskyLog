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

Route::get('/lens/create/{lens}', 'LensController@create')->middleware('verified');

Route::get('/lens/admin', 'LensController@indexAdmin');

Route::resource('lens', 'LensController', ['parameters' => ['lens' => 'lens']])->middleware('verified')->except('show');

Route::get('/lens/{lens}', 'LensController@show');

Route::get('/getLensJson/{id}', 'LensController@getLensJson');

Auth::routes(['verify' => true]);

Route::resource('users', 'UserController')->middleware('isAdmin')->except(['create', 'store']);
