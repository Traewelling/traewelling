<?php

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

use App\Http\Controllers\API\LegacyApi0Controller;
use App\Http\Controllers\API\v1\AuthController as v1Auth;
use App\Http\Controllers\API\v1\EventController;
use App\Http\Controllers\API\v1\FollowController;
use App\Http\Controllers\API\v1\IcsController;
use App\Http\Controllers\API\v1\LikesController;
use App\Http\Controllers\API\v1\NotificationsController;
use App\Http\Controllers\API\v1\PrivacyPolicyController;
use App\Http\Controllers\API\v1\SessionController;
use App\Http\Controllers\API\v1\SettingsController;
use App\Http\Controllers\API\v1\StatisticsController;
use App\Http\Controllers\API\v1\StatusController;
use App\Http\Controllers\API\v1\SupportController;
use App\Http\Controllers\API\v1\TokenController;
use App\Http\Controllers\API\v1\TransportController;
use App\Http\Controllers\API\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'middleware' => ['return-json']], static function() {
    Route::group(['prefix' => 'auth'], function() {
        Route::post('login', [v1Auth::class, 'login']);
        Route::post('signup', [v1Auth::class, 'register']);
        Route::group(['middleware' => 'auth:api'], function() {
            Route::post('refresh', [v1Auth::class, 'refresh']);
            Route::post('logout', [v1Auth::class, 'logout']);
            Route::get('user', [v1Auth::class, 'user']);
        });
    });

    Route::get('static/privacy', [PrivacyPolicyController::class, 'getPrivacyPolicy'])
         ->name('api.v1.getPrivacyPolicy');

    Route::group(['middleware' => ['auth:api', 'privacy-policy']], static function() {
        Route::post('event', [EventController::class, 'suggest']);
        Route::get('activeEvents', [EventController::class, 'activeEvents']);
        Route::get('leaderboard/friends', [StatisticsController::class, 'leaderboardFriends']);
        Route::get('dashboard', [StatusController::class, 'getDashboard']);
        Route::get('dashboard/global', [StatusController::class, 'getGlobalDashboard']);
        Route::get('dashboard/future', [StatusController::class, 'getFutureCheckins']);
        Route::delete('status/{id}', [StatusController::class, 'destroy']);
        Route::put('status/{id}', [StatusController::class, 'update']);
        Route::post('status/{id}/like', [LikesController::class, 'create']);
        Route::delete('status/{id}/like', [LikesController::class, 'destroy']);
        Route::delete('statuses/{id}', [StatusController::class, 'destroy']); //TODO deprecated: Remove this after 2023-02-28 (new: /status/{id})
        Route::put('statuses/{id}', [StatusController::class, 'update']); //TODO deprecated: Remove this after 2023-02-28 (new: /status/{id})
        Route::post('like/{statusId}', [LikesController::class, 'create']);  //TODO deprecated: Remove this after 2023-02-28 (new: /status/{id}/like)
        Route::delete('like/{status}', [LikesController::class, 'destroy']); //TODO deprecated: Remove this after 2023-02-28 (new: /status/{id}/like)
        Route::post('support/ticket', [SupportController::class, 'createTicket']);
        Route::group(['prefix' => 'notifications'], static function() {
            Route::get('/', [NotificationsController::class, 'index']);
            Route::get('count', [NotificationsController::class, 'count']);
            Route::put('{id}', [NotificationsController::class, 'update']);
            Route::put('read/{id}', [NotificationsController::class, 'read']);
            Route::put('unread/{id}', [NotificationsController::class, 'unread']);
            Route::post('readAll', [NotificationsController::class, 'readAll']);
        });
        Route::group(['prefix' => 'trains'], static function() {
            Route::get('trip/', [TransportController::class, 'getTrip']);
            Route::post('checkin', [TransportController::class, 'create']);
            Route::group(['prefix' => 'station'], static function() {
                Route::get('{name}/departures', [TransportController::class, 'departures']);
                Route::put('{name}/home', [TransportController::class, 'setHome']);
                Route::get('nearby', [TransportController::class, 'getNextStationByCoordinates']);
                Route::get('autocomplete/{query}', [TransportController::class, 'getTrainStationAutocomplete']);
                Route::get('history', [TransportController::class, 'getTrainStationHistory']);
            });
        });
        Route::group(['prefix' => 'statistics'], static function() {
            Route::get('/', [StatisticsController::class, 'getPersonalStatistics']);
            Route::get('/global', [StatisticsController::class, 'getGlobalStatistics']);
            Route::post('export', [StatisticsController::class, 'generateTravelExport']);
        });
        Route::group(['prefix' => 'user'], static function() {
            Route::post('createFollow', [FollowController::class, 'createFollow']);
            Route::delete('destroyFollow', [FollowController::class, 'destroyFollow']);
            Route::delete('removeFollower', [FollowController::class, 'removeFollower']);
            Route::delete('rejectFollowRequest', [FollowController::class, 'rejectFollowRequest']);
            Route::put('approveFollowRequest', [FollowController::class, 'approveFollowRequest']);
            Route::post('/{userId}/block', [UserController::class, 'createBlock']);
            Route::delete('/{userId}/block', [UserController::class, 'destroyBlock']);
            Route::post('/{userId}/mute', [UserController::class, 'createMute']);
            Route::delete('/{userId}/mute', [UserController::class, 'destroyMute']);
            Route::post('createMute', [UserController::class, 'createMute']);//TODO deprecated: Remove this after 2023-02-28 (new: /user/{id}/mute)
            Route::delete('destroyMute', [UserController::class, 'destroyMute']);//TODO deprecated: Remove this after 2023-02-28 (new: /user/{id}/mute)
            Route::get('search/{query}', [UserController::class, 'search']);
            Route::get('statuses/active', [StatusController::class, 'getActiveStatus']);
        });
        Route::group(['prefix' => 'settings'], static function() {
            Route::put('acceptPrivacy', [PrivacyPolicyController::class, 'acceptPrivacyPolicy'])
                 ->withoutMiddleware('privacy-policy');
            Route::get('profile', [SettingsController::class, 'getProfileSettings']);
            Route::put('profile', [SettingsController::class, 'updateSettings']);
            Route::delete('profilePicture', [SettingsController::class, 'deleteProfilePicture']);
            Route::post('profilePicture', [SettingsController::class, 'uploadProfilePicture']);
            Route::put('email', [SettingsController::class, 'updateMail']);
            Route::post('email/resend', [SettingsController::class, 'resendMail']);
            Route::put('password', [SettingsController::class, 'updatePassword']);
            Route::delete('account', [UserController::class, 'deleteAccount'])
                 ->withoutMiddleware('privacy-policy');
            Route::get('ics-tokens', [IcsController::class, 'getIcsTokens']);
            Route::post('ics-token', [IcsController::class, 'createIcsToken']);
            Route::delete('ics-token', [IcsController::class, 'revokeIcsToken']);
            Route::get('sessions', [SessionController::class, 'index']);
            Route::delete('sessions', [SessionController::class, 'deleteAllSessions']);
            Route::get('tokens', [TokenController::class, 'index']);
            Route::delete('tokens', [TokenController::class, 'revokeAllTokens']);
            Route::delete('token', [TokenController::class, 'revokeToken']);
            Route::get('followers', [FollowController::class, 'getFollowers']);
            Route::get('follow-requests', [FollowController::class, 'getFollowRequests']);
            Route::get('followings', [FollowController::class, 'getFollowings']);
        });
    });

    Route::group(['middleware' => ['semiguest:api', 'privacy-policy']], static function() {
        Route::get('statuses', [StatusController::class, 'enRoute']);
        Route::get('status/{id}', [StatusController::class, 'show']);
        Route::get('status/{id}/likes', [LikesController::class, 'show']);
        Route::get('statuses/{id}', [StatusController::class, 'show']); //TODO deprecated: Remove this after 2023-02-28 (new: /status/{id})
        Route::get('statuses/{id}/likedby', [LikesController::class, 'show']); //TODO deprecated: Remove this after 2023-02-28 (new: /status/{id}/likedby)
        Route::get('stopovers/{parameters}', [StatusController::class, 'getStopovers']);
        Route::get('polyline/{parameters}', [StatusController::class, 'getPolyline']);
        Route::get('event/{slug}', [EventController::class, 'show']);
        Route::get('event/{slug}/details', [EventController::class, 'showDetails']);
        Route::get('event/{slug}/statuses', [EventController::class, 'statuses']);
        Route::get('events', [EventController::class, 'upcoming']);
        Route::get('user/{username}', [UserController::class, 'show']);
        Route::get('user/{username}/statuses', [UserController::class, 'statuses']);
        Route::get('leaderboard', [StatisticsController::class, 'leaderboard']);
        Route::get('leaderboard/distance', [StatisticsController::class, 'leaderboardByDistance']);
        Route::get('leaderboard/{month}', [StatisticsController::class, 'leaderboardForMonth']);
    });
});

Route::group(['prefix' => 'v0', 'middleware' => ['return-json']], static function() {
    Route::group(['middleware' => ['auth:api', 'privacy']], static function() {
        //Endpoint used between 2022-09-01 and 2022-10-28 (many requests)
        Route::get('getuser', [LegacyApi0Controller::class, 'getUser'])
             ->name('api.v0.getUser');

        //Endpoint used between 2022-09-01 and 2022-10-28 (medium traffic)
        Route::get('/user/{username}', [LegacyApi0Controller::class, 'showUser'])
             ->name('api.v0.user');

        //Endpoint used between 2022-09-01 and 2022-10-28 (many requests)
        Route::get('statuses', [LegacyApi0Controller::class, 'showStatuses'])
             ->name('api.v0.statuses');

        //Endpoint used between 2022-09-01 and 2022-10-28 (very low traffic)
        Route::get('/trains/stationboard', [LegacyApi0Controller::class, 'showStationboard'])
             ->name('api.v0.checkin.train.stationboard');

        //Endpoint used between 2022-09-01 and 2022-10-28 (very low traffic)
        Route::get('/trains/trip', [LegacyApi0Controller::class, 'showTrip'])
             ->name('api.v0.checkin.train.trip');

        //Endpoint used between 2022-09-01 and 2022-10-28 (many requests)
        Route::post('/trains/checkin', [LegacyApi0Controller::class, 'checkin'])
             ->name('api.v0.checkin.train.checkin');

        //Endpoint used between 2022-09-01 and 2022-10-28 (very low traffic)
        Route::get('/trains/nearby', [LegacyApi0Controller::class, 'showStationByCoordinates'])
             ->name('api.v0.trains.nearby');
    });
});
