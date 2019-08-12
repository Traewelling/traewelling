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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::post('/destroy/provider', [
    'uses'  => 'SocialController@destroyProvider',
    'as'    => 'provider.destroy',
    'middleware' => 'auth'
]);

Route::post('/settings', [
    'uses' => 'UserController@updateSettings',
    'as'   => 'settings',
    'middleware' => 'auth'
]);


Route::get('/dashboard', [
    'uses' => 'StatusController@getDashboard',
    'as'   => 'dashboard',
    'middleware' => 'auth'
]);

Route::get('/settings', [
    'uses' => 'UserController@getAccount',
    'as'   => 'settings',
    'middleware' => 'auth'
]);

Route::get('/profile/{username}', [
    'uses' => 'UserController@getProfilePage',
    'as'   => 'account.show'
]);

Route::get('/userimage/{filename}', [
    'uses' => 'UserController@getUserImage',
    'as'   => 'account.image'
]);

Route::post('/createstatus', [
    'uses' => 'StatusController@CreateStatus',
    'as'   => 'status.create',
    'middleware' => 'auth'
]);

Route::delete('/destroystatus', [
    'uses' => 'StatusController@DeleteStatus',
    'as'   => 'status.delete',
    'middleware' => 'auth'
]);


Route::post('/edit', [
    'uses' => 'StatusController@EditStatus',
    'as' => 'edit',
    'middleware' => 'auth'
]);

Route::post('/createlike', [
    'uses' => 'StatusController@createLike',
    'as'   => 'like.create',
    'middleware' => 'auth'
]);

Route::post('/destroylike', [
    'uses' => 'StatusController@destroyLike',
    'as'   => 'like.destroy',
    'middleware' => 'auth'
]);

Route::post('/createfollow', [
    'uses' => 'UserController@CreateFollow',
    'as'   => 'follow.create',
    'middleware' => 'auth'
]);

Route::post('/destroyfollow', [
    'uses' => 'UserController@DestroyFollow',
    'as'   => 'follow.destroy',
    'middleware' => 'auth'
]);


