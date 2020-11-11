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

Route::group(['prefix' => 'v0', 'middleware' => 'return-json'], function (){
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
            Route::get('leaderboard', 'API\UserController@getLeaderboard')->name('api.v0.user.leaderboard');
            Route::get('{username}', 'API\UserController@show')->name('api.v0.user');
            Route::get('search/{query}', 'API\UserController@searchUser')->name('api.v0.user.search');
            Route::get('{username}/active', 'API\UserController@active')->name('api.v0.user.active');
            Route::put('profilepicture', 'API\UserController@PutProfilepicture')->name('api.v0.user.profilepicture');
            Route::put('displayname', 'API\UserController@PutDisplayname')->name('api.v0.user.displayname');
        });

        // Controller for complete /statuses-stuff
        Route::group(['prefix' => 'statuses'], function () {
            Route::get('enroute/all', 'API\StatusController@enroute')->name('api.v0.statuses.enroute');
            Route::get('event/{statusId}', 'API\StatusController@getByEvent')->name('api.v0.statuses.event');
            Route::post('{statusId}/like', 'API\StatusController@createLike')->name('api.v0.statuses.like');
            Route::delete('{statusId}/like', 'API\StatusController@destroyLike');
            Route::get('{statusId}/likes', 'API\StatusController@getLikes')->name('api.v0.statuses.likes');
        });
        Route::resource('statuses', 'API\StatusController', ['as' => 'api.v0']);

        Route::resource('notifications', 'API\NotificationController');

        // Controller for complete Train-Transport-Stuff
        Route::group(['prefix' => 'trains'], function() {
            Route::get('autocomplete/{station}', 'API\TransportController@TrainAutocomplete')->name('api.v0.checkin.train.autocomplete');
            Route::get('stationboard', 'API\TransportController@TrainStationboard')->name('api.v0.checkin.train.stationboard');;
            Route::get('trip', 'API\TransportController@TrainTrip')->name('api.v0.checkin.train.trip');
            Route::post('checkin', 'API\TransportController@TrainCheckin')->name('api.v0.checkin.train.checkin');
            Route::get('latest', 'API\TransportController@TrainLatestArrivals')->name('api.v0.checkin.train.latest');
            Route::get('home', 'API\TransportController@getHome')->name('api.v0.checkin.train.home');
            Route::put('home', 'API\TransportController@setHome');
            Route::get('nearby', 'API\TransportController@StationByCoordinates')->name('api.v0.trains.nearby');
        });
    });
});
