<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v0'], function (){
    Route::group(['middleware' => ['guest:api']], function () {
        Route::group(['prefix' => 'auth'], function () {
            Route::post('login', 'API\AuthController@login')->name('api.v0.auth.login');
            Route::post('signup', 'API\AuthController@signup')->name('api.v0.auth.signup');
        });
    });
    Route::put('user/accept_privacy', 'PrivacyAgreementController@ack')->middleware('auth:api')
        ->name('api.v0.user.accept_privacy');
    // All protected routes
    Route::group(['middleware' => ['auth:api', 'privacy']], function() {
        Route::post('auth/logout', 'API\AuthController@logout')->name('api.v0.auth.logout');
        Route::get('getuser', 'API\AuthController@getUser')->name('api.v0.getUser');

        Route::group(['prefix' => 'user'], function() {
            Route::get('{username}', 'API\UserController@show')->name('api.v0.user');
            Route::get('{username}/active', 'API\UserController@active')->name('api.v0.user.active');
        });

        // Controller for complete /statuses-stuff
        Route::get('statuses/enroute', 'API\StatusController@enroute')->name('api.v0.statuses.enroute');
        Route::resource('statuses', 'API\StatusController', ['as' => 'api.v0']);
        Route::get('statuses/event/{slug}', 'API\StatusController@getByEvent')->name('api.v0.statuses.event');

        Route::resource('notifications', 'API\NotificationController');

        // Controller for complete Train-Transport-Stuff
        Route::group(['prefix' => 'trains'], function() {
            Route::get('autocomplete/{station}', 'API\TransportController@TrainAutocomplete');
            Route::get('stationboard', 'API\TransportController@TrainStationboard');
            Route::get('trip', 'API\TransportController@TrainTrip');
            Route::post('checkin', 'API\TransportController@TrainCheckin');
            Route::get('latest', 'API\TransportController@TrainLatestArrivals');
            Route::get('home', 'API\TransportController@getHome');
            Route::put('home', 'API\TransportController@setHome');
        });
        Route::group(['prefix' => 'user'], function() {
            Route::put('profilepicture', 'API\UserController@PutProfilepicture')->name('api.v0.user.profilepicture');
            Route::put('displayname', 'API\UserController@PutDisplayname')->name('api.v0.user.displayname');
        });
    });
});
