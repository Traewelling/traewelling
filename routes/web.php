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



Route::get('/lang/{lang?}', function($lang=NULL){
    Session::put('language', $lang);
    return Redirect::back();
})->name('lang');

Route::get('/', function () {
    return view('welcome');
})->middleware('guest')->name('welcome');


Route::get('/imprint', function() {
    return view('imprint');
})->name('imprint');

Route::get('/privacy', function() {
    return view('privacy');
})->name('privacy');

Route::get('/about', function() {
    return view('about');
})->name('about');

Route::get('/profile/{username}', [
    'uses' => 'UserController@getProfilePage',
    'as'   => 'account.show'
]);

Route::get('/userimage/{filename}', [
    'uses' => 'UserController@getUserImage',
    'as'   => 'account.image'
]);

Route::get('/leaderboard', [
    'uses' => 'UserController@getLeaderboard',
    'as'   => 'leaderboard',
]);

Route::get('/statuses/active', [
    'uses' => 'StatusController@getActiveStatuses',
    'as'   => 'statuses.active',
]);

Auth::routes(['verify' => true]);

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');
Route::get('/status/{id}', 'StatusController@getStatus');

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


Route::middleware(['auth'])->group(function() {

    Route::post('/destroy/provider', [
        'uses'  => 'SocialController@destroyProvider',
        'as'    => 'provider.destroy',
    ]);

    Route::post('/settings/password', [
        'uses' => 'UserController@updatePassword',
        'as'   => 'password.change',
    ]);

    Route::post('/settings', [
        'uses' => 'UserController@updateSettings',
        'as'   => 'settings',
    ]);

    Route::get('/destroy', [
        'uses' => 'UserController@destroyUser',
        'as'   => 'account.destroy',
    ]);

    Route::get('/dashboard', [
        'uses' => 'StatusController@getDashboard',
        'as'   => 'dashboard',
    ]);

    Route::get('/dashboard/global', [
        'uses' => 'StatusController@getGlobalDashboard',
        'as'   => 'globaldashboard',
    ]);

    Route::get('/settings', [
        'uses' => 'UserController@getAccount',
        'as'   => 'settings',
    ]);

    Route::get('/delsession', [
        'uses' => 'UserController@deleteSession',
        'as'   => 'delsession',
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

    Route::get('/export', [
        'uses' => 'StatusController@exportLanding',
        'as'   => 'export.landing',
    ]);
    Route::get('/exportCSV', [
        'uses' => 'StatusController@exportCSV',
        'as'   => 'export.csv',
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

    Route::get('/trains/stationboard', [
        'uses'  => 'TransportController@trainStationboard',
        'as'    => 'trains.stationboard',
    ]);

    Route::get('/trains/trip', [
        'uses'  => 'TransportController@trainTrip',
        'as'    => 'trains.trip'
    ]);

    Route::post('/trains/checkin', [
        'uses'  => 'TransportController@trainCheckin',
        'as'    => 'trains.checkin'
    ]);

    Route::get('/trains/setHome/{ibnr}', [
        'uses'  => 'TransportController@setHome',
        'as'    => 'user.setHome'
    ]);

    Route::get('/busses/stationboard', [
        'uses'  => 'TransportController@trainStationboard',
        'as'    => 'busses.stationboard'
    ]);

    Route::get('/mastodon/test', [
        'uses'  => 'SocialController@testMastodon',
    ]);

});
//Route::get('/trip', 'HafasTripController@getTrip')->defaults('tripID', '1|178890|0|80|13082019')->defaults('lineName', 'ICE 376');
