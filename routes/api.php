<?php

use App\Http\Controllers\API\StatusController;
use App\Http\Controllers\API\TransportController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\PrivacyAgreementController;
use \App\Http\Controllers\API\AuthController;

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

Route::group(['prefix' => 'v0', 'middleware' => 'return-json'], function() {
    Route::group(['middleware' => ['guest:api']], function() {
        Route::group(['prefix' => 'auth'], function() {
            Route::post('login', [AuthController::class, 'login'])
                 ->name('api.v0.auth.login');
            Route::post('signup', [AuthController::class, 'signup'])
                 ->name('api.v0.auth.signup');
        });
    });
    Route::put('user/accept_privacy', [PrivacyAgreementController::class, 'ack'])
         ->middleware('auth:api')
         ->name('api.v0.user.accept_privacy');
    // All protected routes
    Route::group(['middleware' => ['auth:api', 'privacy']], function() {
        Route::post('auth/logout', [AuthController::class, 'logout'])
             ->name('api.v0.auth.logout');
        Route::get('getuser', [AuthController::class, 'getUser'])
             ->name('api.v0.getUser');

        Route::group(['prefix' => 'user'], function() {
            Route::get('leaderboard', [UserController::class, 'getLeaderboard'])
                 ->name('api.v0.user.leaderboard');
            Route::get('{username}', [UserController::class, 'show'])
                 ->name('api.v0.user');
            Route::get('{username}/active', [UserController::class, 'active'])
                 ->name('api.v0.user.active');
            Route::put('profilepicture', [UserController::class, 'PutProfilepicture'])
                 ->name('api.v0.user.profilepicture');
            Route::put('displayname', [UserController::class, 'PutDisplayname'])
                 ->name('api.v0.user.displayname');
        });

        // Controller for complete /statuses-stuff
        Route::group(['prefix' => 'statuses'], function() {
            Route::get('enroute/all', [StatusController::class, 'enroute'])
                 ->name('api.v0.statuses.enroute');
            Route::get('event/{statusId}', [StatusController::class, 'getByEvent'])
                 ->name('api.v0.statuses.event');
            Route::post('{statusId}/like', [StatusController::class, 'createLike'])
                 ->name('api.v0.statuses.like');
            Route::delete('{statusId}/like', [StatusController::class, 'destroyLike']);
            Route::get('{statusId}/likes', [StatusController::class, 'getLikes'])
                 ->name('api.v0.statuses.likes');
        });

        // So Laravel decided that it's a good idea to use fixed namespaces in the Resource-Routes which breaks
        // imports. For normal routes this is not a problem but here I need to either escape the Route with a \ or
        // not include it at all.
        // I hate laravel so much for this.
        Route::resource('statuses', 'API\StatusController', ['as' => 'api.v0']);
        Route::resource('notifications', 'API\NotificationController');


        // Controller for complete Train-Transport-Stuff
        Route::group(['prefix' => 'trains'], function() {
            Route::get('autocomplete/{station}', [TransportController::class, 'TrainAutocomplete'])
                 ->name('api.v0.checkin.train.autocomplete');
            Route::get('stationboard', [TransportController::class, 'TrainStationboard'])
                 ->name('api.v0.checkin.train.stationboard');
            Route::get('trip', [TransportController::class, 'TrainTrip'])
                 ->name('api.v0.checkin.train.trip');
            Route::post('checkin', [TransportController::class, 'TrainCheckin'])
                 ->name('api.v0.checkin.train.checkin');
            Route::get('latest', [TransportController::class, 'TrainLatestArrivals'])
                 ->name('api.v0.checkin.train.latest');
            Route::get('home', [TransportController::class, 'getHome'])
                 ->name('api.v0.checkin.train.home');
            Route::put('home', [TransportController::class, 'setHome']);
            Route::get('nearby', [TransportController::class, 'StationByCoordinates'])
                 ->name('api.v0.trains.nearby');
        });
    });
});
