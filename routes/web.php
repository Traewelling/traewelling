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
})->middleware('guest');

Auth::routes();

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');



Route::middleware(['auth'])->group(function() {

    Route::post('/destroy/provider', [
        'uses'  => 'SocialController@destroyProvider',
        'as'    => 'provider.destroy',
    ]);

    Route::post('/settings', [
        'uses' => 'UserController@updateSettings',
        'as'   => 'settings',
    ]);

    Route::get('/dashboard', [
        'uses' => 'StatusController@getDashboard',
        'as'   => 'dashboard',
    ]);

    Route::get('/settings', [
        'uses' => 'UserController@getAccount',
        'as'   => 'settings',
    ]);

    Route::post('/createstatus', [
        'uses' => 'StatusController@CreateStatus',
        'as'   => 'status.create',
    ]);

    Route::delete('/destroystatus', [
        'uses' => 'StatusController@DeleteStatus',
        'as'   => 'status.delete',
    ]);

    Route::post('/edit', [
        'uses' => 'StatusController@EditStatus',
        'as' => 'edit',
    ]);

    Route::post('/createlike', [
        'uses' => 'StatusController@createLike',
        'as'   => 'like.create',
    ]);

    Route::post('/destroylike', [
        'uses' => 'StatusController@destroyLike',
        'as'   => 'like.destroy',
    ]);

    Route::post('/createfollow', [
        'uses' => 'UserController@CreateFollow',
        'as'   => 'follow.create',
    ]);

    Route::post('/destroyfollow', [
        'uses' => 'UserController@DestroyFollow',
        'as'   => 'follow.destroy',
    ]);

    Route::get('/transport/train/autocomplete/{station}', [
        'uses'  => 'TransportController@TrainAutocomplete',
        'as'    => 'transport.train.autocomplete',
    ]);

    Route::get('/transport/bus/autocomplete/{station}', [
        'uses'  => 'TransportController@BusAutocomplete',
        'as'    => 'transport.bus.autocomplete',
    ]);

    Route::get('/stationboard', [
        'uses'  => 'TransportController@stationboard',
        'as'    => 'trains.stationboard',
    ]);

    Route::get('/trip', [
        'uses'  => 'TransportController@trip',
        'as'    => 'trains.trip'
    ]);
});
//Route::get('/trip', 'HafasTripController@getTrip')->defaults('tripID', '1|178890|0|80|13082019')->defaults('lineName', 'ICE 376');

Route::get('/profile/{username}', [
    'uses' => 'UserController@getProfilePage',
    'as'   => 'account.show'
]);

Route::get('/userimage/{filename}', [
    'uses' => 'UserController@getUserImage',
    'as'   => 'account.image'
]);
