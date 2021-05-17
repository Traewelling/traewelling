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

use App\Http\Controllers\BlogController;
use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\StatisticController;
use App\Http\Controllers\Frontend\SettingsController;
use App\Http\Controllers\FrontendEventController;
use App\Http\Controllers\FrontendStaticController;
use App\Http\Controllers\FrontendStatusController;
use App\Http\Controllers\FrontendTransportController;
use App\Http\Controllers\FrontendUserController;
use App\Http\Controllers\IcsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrivacyAgreementController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendStaticController::class, 'renderLandingPage'])
     ->name('static.welcome');

Route::view('/about', 'about')->name('static.about');
Route::view('/imprint', 'imprint')->name('static.imprint');

Route::get('/privacy', [PrivacyAgreementController::class, 'intercept'])
     ->name('static.privacy');

Route::get('/profile/{username}', [FrontendUserController::class, 'getProfilePage'])
     ->name('account.show');

Route::get('/profile/{username}/profilepicture', [FrontendUserController::class, 'getProfilePicture'])
     ->name('account.showProfilePicture');

Route::get('/leaderboard', [FrontendUserController::class, 'getLeaderboard'])
     ->name('leaderboard');

Route::get('/leaderboard/{date}', [FrontendUserController::class, 'renderMonthlyLeaderboard'])
     ->name('leaderboard.month');

Route::get('/statuses/active', [FrontendStatusController::class, 'getActiveStatuses'])
     ->name('statuses.active');

Route::get('/statuses/event/{eventSlug}', [FrontendStatusController::class, 'statusesByEvent'])
     ->name('statuses.byEvent');

Auth::routes(['verify' => true]);

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::get('/status/{id}', [FrontendStatusController::class, 'getStatus'])
     ->name('statuses.get');

Route::prefix('blog')->group(function() {
    Route::get('/', [BlogController::class, 'all'])
         ->name('blog.all');

    Route::get('/{slug}', [BlogController::class, 'show'])
         ->name('blog.show');

    Route::get('/cat/{category}', [BlogController::class, 'category'])
         ->name('blog.category');
});

/**
 * These routes can be used by logged in users although they have not signed the privacy policy yet.
 */
Route::middleware(['auth'])->group(function() {

    Route::get('/gdpr-intercept', [PrivacyAgreementController::class, 'intercept'])
         ->name('gdpr.intercept');

    Route::post('/gdpr-ack', [PrivacyAgreementController::class, 'ack'])
         ->name('gdpr.ack');

    Route::post('/settings/destroy', [AccountController::class, 'deleteUserAccount'])
         ->name('account.destroy');
});

/**
 * Routes for the admins.
 */
Route::prefix('admin')->middleware(['auth', 'userrole:5'])->group(function() {

    Route::get('/', [FrontendStatusController::class, 'usageboard'])
         ->name('admin.dashboard');

    Route::get('/events', [FrontendEventController::class, 'index'])
         ->name('events.all');

    Route::get('/events/new', [FrontendEventController::class, 'newForm'])
         ->name('events.newform');

    Route::post('/events/new', [FrontendEventController::class, 'store'])
         ->name('events.store');

    Route::get('/events/{slug}/delete', [FrontendEventController::class, 'destroy'])
         ->name('events.delete');

    Route::get('/events/{slug}', [FrontendEventController::class, 'show'])
         ->name('events.show');

    Route::put('/events/{slug}', [FrontendEventController::class, 'update'])
         ->name('events.update');
});

Route::get('/ics', [IcsController::class, 'renderIcs'])
     ->name('ics');

/**
 * All of these routes can only be used by fully registered users.
 */
Route::middleware(['auth', 'privacy'])->group(function() {
    Route::post('/ics/createToken', [IcsController::class, 'createIcsToken'])
         ->name('ics.createToken');
    Route::post('/ics/revokeToken', [IcsController::class, 'revokeIcsToken'])
         ->name('ics.revokeToken');

    Route::post('/destroy/provider', [SocialController::class, 'destroyProvider'])
         ->name('provider.destroy');

    Route::get('/stats', [StatisticController::class, 'renderMainStats'])
         ->name('stats');

    Route::prefix('settings')->group(function() {
        Route::get('/', [SettingsController::class, 'renderSettings'])
             ->name('settings');
        Route::post('/', [SettingsController::class, 'updateMainSettings']);
        Route::post('/update/privacy', [SettingsController::class, 'updatePrivacySettings'])
             ->name('settings.privacy');

        Route::post('/password', [SettingsController::class, 'updatePassword'])
             ->name('password.change');

        Route::get('/follower', [\App\Http\Controllers\SettingsController::class, 'renderFollowerSettings'])
             ->name('settings.follower');
        Route::post('/follower/remove', [\App\Http\Controllers\SettingsController::class, 'removeFollower'])
             ->name('settings.follower.remove');
        Route::post('/follower/approve', [\App\Http\Controllers\Frontend\SettingsController::class, 'approveFollower'])
             ->name('settings.follower.approve');
        Route::post('/follower/reject', [\App\Http\Controllers\Frontend\SettingsController::class, 'rejectFollower'])
             ->name('settings.follower.reject');

        Route::post('/uploadProfileImage', [FrontendUserController::class, 'updateProfilePicture'])
             ->name('settings.upload-image');
        Route::get('/deleteProfilePicture', [UserController::class, 'deleteProfilePicture'])
             ->name('settings.delete-profile-picture');

        Route::post('/delsession', [UserController::class, 'deleteSession'])
             ->name('delsession');
        Route::post('/deltoken', [UserController::class, 'deleteToken'])
             ->name('deltoken');
    });

    Route::get('/dashboard', [FrontendStatusController::class, 'getDashboard'])
         ->name('dashboard');

    Route::get('/dashboard/global', [FrontendStatusController::class, 'getGlobalDashboard'])
         ->name('globaldashboard');

    Route::delete('/destroystatus', [FrontendStatusController::class, 'DeleteStatus'])
         ->name('status.delete');

    Route::post('/edit', [FrontendStatusController::class, 'EditStatus'])
         ->name('edit');

    Route::post('/createlike', [FrontendStatusController::class, 'createLike'])
         ->name('like.create');

    Route::post('/destroylike', [FrontendStatusController::class, 'DestroyLike'])
         ->name('like.destroy');

    Route::get('/export', [FrontendStatusController::class, 'exportLanding'])
         ->name('export.landing');

    Route::get('/export-generate', [FrontendStatusController::class, 'export'])
         ->name('export.generate');

    Route::post('/createfollow', [FrontendUserController::class, 'CreateFollow'])
         ->name('follow.create');

    Route::post('/requestfollow', [FrontendUserController::class, 'requestFollow'])
         ->name('follow.request');

    Route::post('/destroyfollow', [FrontendUserController::class, 'destroyFollow'])
         ->name('follow.destroy');

    Route::get('/transport/train/autocomplete/{station}', [FrontendTransportController::class, 'TrainAutocomplete'])
         ->name('transport.train.autocomplete');

    Route::get('/transport/bus/autocomplete/{station}', [FrontendTransportController::class, 'BusAutocomplete'])
         ->name('transport.bus.autocomplete');

    Route::get('/trains/stationboard', [FrontendTransportController::class, 'TrainStationboard'])
         ->name('trains.stationboard');

    Route::get('/trains/nearby', [FrontendTransportController::class, 'StationByCoordinates'])
         ->name('trains.nearby');

    Route::get('/trains/trip', [FrontendTransportController::class, 'TrainTrip'])
         ->name('trains.trip');

    Route::get('/trains/fast', [FrontendTransportController::class, 'FastTripAccess'])
         ->name('trains.fast');

    Route::post('/trains/checkin', [FrontendTransportController::class, 'TrainCheckin'])
         ->name('trains.checkin');

    Route::get('/trains/setHome/', [FrontendTransportController::class, 'setHome'])
         ->name('user.setHome');

    Route::get('/busses/stationboard', [FrontendTransportController::class, 'TrainStationboard'])
         ->name('busses.stationboard');

    Route::get('/mastodon/test', [SocialController::class, 'testMastodon']);

    Route::get('/notifications/latest', [NotificationController::class, 'renderLatest'])
         ->name('notifications.latest');

    Route::post('/notifications/toggleReadState/{id}', [NotificationController::class, 'toggleReadState'])
         ->name('notifications.toggleReadState');

    Route::post('/notifications/readAll', [NotificationController::class, 'readAll'])
         ->name('notifications.readAll');

    Route::get('/search/', [FrontendUserController::class, 'searchUser'])
         ->name('userSearch');

    Route::post('/user/mute', [\App\Http\Controllers\Frontend\UserController::class, 'muteUser'])
         ->name('user.mute');
    Route::post('/user/unmute', [\App\Http\Controllers\Frontend\UserController::class, 'unmuteUser'])
         ->name('user.unmute');
});
