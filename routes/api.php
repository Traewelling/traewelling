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
use App\Http\Controllers\API\v1\StatusTagController;
use App\Http\Controllers\API\v1\SupportController;
use App\Http\Controllers\API\v1\TokenController;
use App\Http\Controllers\API\v1\TransportController;
use App\Http\Controllers\API\v1\UserController;
use App\Http\Controllers\API\v1\WebhookController;
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
        Route::post('event', [EventController::class, 'suggest'])->middleware(['scope:write-event-suggestions']);
        Route::get('activeEvents', [EventController::class, 'activeEvents'])->middleware(['scope:read-statuses']);
        Route::get('leaderboard/friends', [StatisticsController::class, 'leaderboardFriends'])
             ->middleware(['scope:read-statistics']);
        Route::group(['middleware' => ['scope:read-statuses']], static function() {
            Route::get('dashboard', [StatusController::class, 'getDashboard']);
            Route::get('dashboard/global', [StatusController::class, 'getGlobalDashboard']);
            Route::get('dashboard/future', [StatusController::class, 'getFutureCheckins']);
        });
        Route::group(['middleware' => ['scope:write-statuses']], static function() {
            Route::delete('status/{id}', [StatusController::class, 'destroy'])->whereNumber('id');
            Route::put('status/{id}', [StatusController::class, 'update']);
            Route::post('status/{statusId}/tags', [StatusTagController::class, 'store']);
            Route::put('status/{statusId}/tags/{tagKey}', [StatusTagController::class, 'update']);
            Route::delete('status/{statusId}/tags/{tagKey}', [StatusTagController::class, 'destroy']);
        });
        Route::group(['middleware' => ['scope:write-likes']], static function() {
            Route::post('status/{id}/like', [LikesController::class, 'create']);
            Route::delete('status/{id}/like', [LikesController::class, 'destroy']);
        });
        Route::post('support/ticket', [SupportController::class, 'createTicket']);
        Route::group(['prefix' => 'notifications'], static function() {
            Route::group(['middleware' => ['scope:read-notifications']], static function() {
                Route::get('/', [NotificationsController::class, 'listNotifications']);
                Route::get('/unread/count', [NotificationsController::class, 'getUnreadCount']);
            });
            Route::group(['middleware' => ['scope:write-notifications']], static function() {
                Route::put('read/all', [NotificationsController::class, 'markAllAsRead']);
                Route::put('read/{id}', [NotificationsController::class, 'markAsRead']);
                Route::put('unread/{id}', [NotificationsController::class, 'markAsUnread']);
            });
        });
        Route::group(['prefix' => 'trains', 'middleware' => ['scope:write-statuses']], static function() {
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
        Route::group(['prefix' => 'statistics', 'middleware' => 'scope:read-statistics'], static function() {
            Route::get('/', [StatisticsController::class, 'getPersonalStatistics']);
            Route::get('/global', [StatisticsController::class, 'getGlobalStatistics']);
            Route::post('export', [StatisticsController::class, 'generateTravelExport'])
                 ->middleware(['scope:write-exports'])->withoutMiddleware(['scope:read-statistics']);
            Route::get('/daily/{date}', [StatisticsController::class, 'getPersonalDailyStatistics']);
        });
        Route::group(['prefix' => 'user'], static function() {
            Route::group(['middleware' => ['scope:write-follows']], static function() {
                Route::post('/{userId}/follow', [FollowController::class, 'createFollow']);
                Route::delete('/{userId}/follow', [FollowController::class, 'destroyFollow']);
            });
            Route::group(['middleware' => ['scope:write-followers']], static function() {
                Route::delete('removeFollower', [FollowController::class, 'removeFollower']);
                Route::delete('rejectFollowRequest', [FollowController::class, 'rejectFollowRequest']);
                Route::put('approveFollowRequest', [FollowController::class, 'approveFollowRequest']);
            });
            Route::group(['middleware' => ['scope:write-blocks']], static function() {
                Route::post('/{userId}/block', [UserController::class, 'createBlock']);
                Route::delete('/{userId}/block', [UserController::class, 'destroyBlock']);
                Route::post('/{userId}/mute', [UserController::class, 'createMute']);
                Route::delete('/{userId}/mute', [UserController::class, 'destroyMute']);
            });
            Route::get('search/{query}', [UserController::class, 'search'])->middleware(['scope:read-search']);
            Route::get('statuses/active', [StatusController::class, 'getActiveStatus'])
                 ->middleware(['scope:read-statuses']);
        });
        Route::group(['prefix' => 'settings'], static function() {
            Route::put('acceptPrivacy', [PrivacyPolicyController::class, 'acceptPrivacyPolicy'])
                 ->withoutMiddleware('privacy-policy');
            Route::get('profile', [SettingsController::class, 'getProfileSettings'])
                 ->middleware(['scope:read-settings']);
            Route::put('profile', [SettingsController::class, 'updateSettings'])
                 ->middleware(['scope:write-settings']);
            Route::delete('profilePicture', [SettingsController::class, 'deleteProfilePicture'])
                 ->middleware(['scope:write-settings-profile-picture']);
            Route::post('profilePicture', [SettingsController::class, 'uploadProfilePicture'])
                 ->middleware(['scope:write-settings-profile-picture']);
            Route::put('email', [SettingsController::class, 'updateMail'])
                 ->middleware(['scope:write-settings-mail']);
            Route::post('email/resend', [SettingsController::class, 'resendMail'])
                 ->middleware(['scope:write-settings-mail']);
            Route::put('password', [SettingsController::class, 'updatePassword'])
                 ->middleware(['scope:extra-write-password']);
            Route::delete('account', [UserController::class, 'deleteAccount'])
                 ->middleware(['scope:extra-delete'])
                 ->withoutMiddleware('privacy-policy');
            Route::group(['middleware' => ['scope:write-settings-calendar']], static function() {
                Route::get('ics-tokens', [IcsController::class, 'getIcsTokens']);
                Route::post('ics-token', [IcsController::class, 'createIcsToken']);
                Route::delete('ics-token', [IcsController::class, 'revokeIcsToken']);
            });
            Route::group(['middleware' => ['scope:extra-terminate-sessions']], static function() {
                Route::get('sessions', [SessionController::class, 'index']);
                Route::delete('sessions', [SessionController::class, 'deleteAllSessions']);
                Route::get('tokens', [TokenController::class, 'index']);
                Route::delete('tokens', [TokenController::class, 'revokeAllTokens']);
                Route::delete('token', [TokenController::class, 'revokeToken']);
            });
            Route::group(['middleware' => ['scope:read-settings-followers']], static function() {
                Route::get('followers', [FollowController::class, 'getFollowers']);
                Route::get('follow-requests', [FollowController::class, 'getFollowRequests']);
                Route::get('followings', [FollowController::class, 'getFollowings']);
            });
        });
        Route::group(['prefix' => 'webhooks'], static function() {
            Route::get('/', [WebhookController::class, 'getWebhooks']);
            Route::get('/{webhookId}', [WebhookController::class, 'getWebhook']);
            Route::delete('/{webhookId}', [WebhookController::class, 'deleteWebhook']);
        });
    });

    Route::group(['middleware' => ['privacy-policy']], static function() {
        Route::group(['middleware' => ['semiscope:read-statuses']], static function() {
            Route::get('statuses', [StatusController::class, 'enRoute']);
            Route::get('status/{id}', [StatusController::class, 'show']);
            Route::get('status/{id}/likes', [LikesController::class, 'show']);
            Route::get('status/{statusId}/tags', [StatusTagController::class, 'index']);
            Route::get('stopovers/{parameters}', [StatusController::class, 'getStopovers']);
            Route::get('polyline/{parameters}', [StatusController::class, 'getPolyline']);
            Route::get('event/{slug}', [EventController::class, 'show']);
            Route::get('event/{slug}/details', [EventController::class, 'showDetails']);
            Route::get('event/{slug}/statuses', [EventController::class, 'statuses']);
            Route::get('events', [EventController::class, 'upcoming']);
            Route::get('user/{username}', [UserController::class, 'show']);
            Route::get('user/{username}/statuses', [UserController::class, 'statuses']);
        });
        Route::group(['middleware' => ['semiscope:read-statistics']], static function() {
            Route::get('leaderboard', [StatisticsController::class, 'leaderboard']);
            Route::get('leaderboard/distance', [StatisticsController::class, 'leaderboardByDistance']);
            Route::get('leaderboard/{month}', [StatisticsController::class, 'leaderboardForMonth']);
        });
    });
});
