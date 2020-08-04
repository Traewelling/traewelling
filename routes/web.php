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


Route::get('/lang/{lang?}', [
    'uses' => 'FrontendStaticController@changeLanguage',
    'as' => 'static.lang'
]);

Route::get('/', [
    'uses' => 'FrontendStaticController@showFrontpage',
    'as' => 'static.welcome',
    'middleware' => 'guest'
]);

Route::get('/imprint', [
    'uses' => 'FrontendStaticController@showImprint',
    'as' => 'static.imprint'
]);

Route::get('/privacy', [
    'uses' => 'PrivacyAgreementController@intercept',
    'as' => 'static.privacy'
]);

Route::get('/about', [
    'uses' => 'FrontendStaticController@showAbout',
    'as' => 'static.about'
]);

Route::get('/profile/{username}', [
    'uses' => 'FrontendUserController@getProfilePage',
    'as'   => 'account.show'
]);

Route::get('/profile/{username}/profilepicture', [
    'uses' => 'FrontendUserController@getProfilePicture',
    'as'   => 'account.showProfilePicture'
]);

Route::get('/leaderboard', [
    'uses' => 'FrontendUserController@getLeaderboard',
    'as'   => 'leaderboard',
]);

Route::get('/statuses/active', [
    'uses' => 'FrontendStatusController@getActiveStatuses',
    'as'   => 'statuses.active',
]);
Route::get('/statuses/event/{event}', [
    'uses'  => 'FrontendStatusController@statusesByEvent',
    'as'    => 'statuses.byEvent'
]);

Auth::routes(['verify' => true]);

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');
Route::get('/status/{id}', [
    'uses' => 'FrontendStatusController@getStatus',
    'as' => 'statuses.get'
]);

Route::get('/blog', [
    'uses'  => 'BlogController@all',
    'as'    => 'blog.all'
]);
Route::get('/blog/{slug}', [
    'uses'  => 'BlogController@show',
    'as'    => 'blog.show'
]);
Route::get('/blog/cat/{cat}', [
    'uses'  => 'BlogController@category',
    'as'    => 'blog.category'
]);

/**
 * These routes can be used by logged in users although they have not signed the privacy policy yet.
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/gdpr-intercept', [
        'uses' => 'PrivacyAgreementController@intercept',
        'as'   => 'gdpr.intercept'
    ]);

    Route::post('/gdpr-ack', [
        'uses' => 'PrivacyAgreementController@ack',
        'as'   => 'gdpr.ack'
    ]);

    Route::get('/settings/destroy', [
        'uses' => 'UserController@destroyUser',
        'as'   => 'account.destroy',
    ]);
});

/**
 * Routes for the admins.
 */
Route::prefix('admin')->middleware(['auth', 'userrole:5'])->group(function () {

    Route::get('/', [
        'uses'  => 'FrontendStatusController@usageboard',
        'as'    => 'admin.dashboard'
    ]);

    Route::get('/events', [
        'uses'  => 'FrontendEventController@index',
        'as'    => 'events.all'
    ]);

    Route::get('/events/new', [
        'uses'  => 'FrontendEventController@newForm',
        'as'    => 'events.newform'
    ]);

    Route::post('/events/new', [
        'uses'  => 'FrontendEventController@store',
        'as'    => 'events.store'
    ]);

    Route::get('/events/{slug}/delete', [
        'uses'  => 'FrontendEventController@destroy',
        'as'    => 'events.delete'
    ]);

    Route::get('/events/{slug}', [
        'uses'  => 'FrontendEventController@show',
        'as'    => 'events.show'
    ]);
    Route::put('/events/{slug}', [
        'uses'  => 'FrontendEventController@update',
        'as'    => 'events.update'
    ]);
});

/**
 * All of these routes can only be used by fully registered users.
 */
Route::middleware(['auth', 'privacy'])->group(function() {

    Route::post('/destroy/provider', [
        'uses'  => 'SocialController@destroyProvider',
        'as'    => 'provider.destroy',
    ]);

    Route::post('/settings/password', [
        'uses' => 'UserController@updatePassword',
        'as'   => 'password.change',
    ]);

    //this has too much dumb logic, that it'll remain inside of the UserController...
    //will leave settings inside of UserController...
    Route::get('/settings', [
        'uses' => 'UserController@getAccount',
        'as'   => 'settings',
    ]);

    Route::post('/settings', [
        'uses' => 'UserController@updateSettings',
        'as'   => 'settings',
    ]);

    Route::post('/settings/uploadProfileImage', [
        'uses' => 'FrontendUserController@updateProfilePicture',
        'as'   => 'settings.upload-image'
    ]);

    Route::get('/settings/deleteProfilePicture', [
        'uses' => 'UserController@deleteProfilePicture',
        'as' => 'settings.delete-profile-picture'
    ]);

    Route::get('/settings/delsession', [
        'uses' => 'UserController@deleteSession',
        'as'   => 'delsession',
    ]);

    Route::get('/settings/deltoken/{id}', [
        'uses' => 'UserController@deleteToken',
        'as'   => 'deltoken',
    ]);

    Route::get('/dashboard', [
        'uses' => 'FrontendStatusController@getDashboard',
        'as'   => 'dashboard',
    ]);

    Route::get('/dashboard/global', [
        'uses' => 'FrontendStatusController@getGlobalDashboard',
        'as'   => 'globaldashboard',
    ]);

    Route::delete('/destroystatus', [
        'uses' => 'FrontendStatusController@DeleteStatus',
        'as'   => 'status.delete',
    ]);

    Route::post('/edit', [
        'uses' => 'FrontendStatusController@EditStatus',
        'as' => 'edit',
    ]);

    Route::post('/createlike', [
        'uses' => 'FrontendStatusController@CreateLike',
        'as'   => 'like.create',
    ]);

    Route::post('/destroylike', [
        'uses' => 'FrontendStatusController@DestroyLike',
        'as'   => 'like.destroy',
    ]);

    Route::get('/export', [
        'uses' => 'FrontendStatusController@exportLanding',
        'as'   => 'export.landing',
    ]);
    Route::get('/export-generate', [
        'uses' => 'FrontendStatusController@export',
        'as'   => 'export.generate',
    ]);

    Route::post('/createfollow', [
        'uses' => 'FrontendUserController@CreateFollow',
        'as'   => 'follow.create',
    ]);

    Route::post('/destroyfollow', [
        'uses' => 'FrontendUserController@DestroyFollow',
        'as'   => 'follow.destroy',
    ]);


    Route::get('/transport/train/autocomplete/{station}', [
        'uses'  => 'FrontendTransportController@TrainAutocomplete',
        'as'    => 'transport.train.autocomplete',
    ]);

    Route::get('/transport/bus/autocomplete/{station}', [
        'uses'  => 'FrontendTransportController@BusAutocomplete',
        'as'    => 'transport.bus.autocomplete',
    ]);

    Route::get('/trains/stationboard', [
        'uses'  => 'FrontendTransportController@TrainStationboard',
        'as'    => 'trains.stationboard',
    ]);

    Route::get('/trains/nearby', [
        'uses'  => 'FrontendTransportController@StationByCoordinates',
        'as'    => 'trains.nearby',
    ]);

    Route::get('/trains/trip', [
        'uses'  => 'FrontendTransportController@TrainTrip',
        'as'    => 'trains.trip'
    ]);

    Route::get('/trains/fast', [
        'uses'  => 'FrontendTransportController@FastTripAccess',
        'as'    => 'trains.fast'
    ]);

    Route::post('/trains/checkin', [
        'uses'  => 'FrontendTransportController@TrainCheckin',
        'as'    => 'trains.checkin'
    ]);

    Route::get('/trains/setHome/{ibnr}', [
        'uses'  => 'FrontendTransportController@setHome',
        'as'    => 'user.setHome'
    ]);

    Route::get('/busses/stationboard', [
        'uses'  => 'FrontendTransportController@trainStationboard',
        'as'    => 'busses.stationboard'
    ]);

    Route::get('/mastodon/test', [
        'uses'  => 'SocialController@testMastodon',
    ]);

    Route::get('/notifications/latest', [
        'uses'  => 'NotificationController@renderLatest',
        'as'    => 'notifications.latest'
    ]);

    Route::post('/notifications/toggleReadState/{id}', [
        'uses'  => 'NotificationController@toggleReadState',
        'as'    => 'notifications.toggleReadState'
    ]);
    Route::post('/notifications/readAll', [
        'uses'  => 'NotificationController@readAll',
        'as'    => 'notifications.readAll'
    ]);
});
