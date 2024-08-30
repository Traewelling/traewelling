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

use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\ChangelogController;
use App\Http\Controllers\Frontend\DevController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\IcsController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Frontend\LeaderboardController;
use App\Http\Controllers\Frontend\OpenData\WikidataController;
use App\Http\Controllers\Frontend\SettingsController;
use App\Http\Controllers\Frontend\Social\MastodonController;
use App\Http\Controllers\Frontend\Social\SocialController;
use App\Http\Controllers\Frontend\StatisticController;
use App\Http\Controllers\Frontend\Stats\DailyStatsController;
use App\Http\Controllers\Frontend\Transport\StatusController;
use App\Http\Controllers\Frontend\User\ProfilePictureController;
use App\Http\Controllers\Frontend\VueFrontendController;
use App\Http\Controllers\Frontend\WebFingerController;
use App\Http\Controllers\Frontend\WebhookController;
use App\Http\Controllers\FrontendStatusController;
use App\Http\Controllers\FrontendTransportController;
use App\Http\Controllers\FrontendUserController;
use App\Http\Controllers\PrivacyAgreementController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(base_path('routes/web/admin.php'));

Route::get('/@{username}/picture', [ProfilePictureController::class, 'generateProfilePicture'])
     ->name('profile.picture');

Route::get('/', [LandingPageController::class, 'renderLandingPage'])
     ->name('static.welcome');

Route::permanentRedirect('/about', 'https://help.traewelling.de/faq/');

Route::prefix('legal')->group(function() {
    Route::view('/', 'legal.notice')
         ->name('legal.notice');
    Route::get('/privacy-policy', [PrivacyAgreementController::class, 'intercept'])
         ->name('legal.privacy');
});

Route::get('/@{username}', [FrontendUserController::class, 'getProfilePage'])
     ->name('profile');

Route::get('/leaderboard', [LeaderboardController::class, 'renderLeaderboard'])
     ->name('leaderboard');

Route::get('/leaderboard/{date}', [LeaderboardController::class, 'renderMonthlyLeaderboard'])
     ->name('leaderboard.month');

Route::get('/statuses/active', [FrontendStatusController::class, 'getActiveStatuses'])
     ->name('statuses.active');

Route::get('/event/{slug}', [FrontendStatusController::class, 'statusesByEvent'])
     ->name('event');

Route::get('/events', [EventController::class, 'renderEventOverview'])
     ->name('events');

Route::get('/changelog', [ChangelogController::class, 'renderChangelog'])
     ->name('changelog');

Auth::routes(['verify' => true]);

Route::get('/auth/redirect/mastodon', [MastodonController::class, 'redirect']);
Route::get('/callback/mastodon', [MastodonController::class, 'callback']);

Route::get('/status/{id}', [FrontendStatusController::class, 'getStatus'])
     ->whereNumber('id')
     ->name('status');

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


Route::get('/ics', [IcsController::class, 'renderIcs'])
     ->name('ics');

/**
 * All of these routes can only be used by fully registered users.
 */
Route::middleware(['auth', 'privacy'])->group(function() {

    Route::view('/trip/create', 'beta.trip-creation')
         ->middleware(['can:create-manual-trip'])
         ->name('trip.create');

    Route::view('/report', 'report')
         ->name('report');

    Route::post('/ics/createToken', [IcsController::class, 'createIcsToken'])
         ->name('ics.createToken'); //TODO: Replace with API Endpoint
    Route::post('/ics/revokeToken', [IcsController::class, 'revokeIcsToken'])
         ->name('ics.revokeToken'); //TODO: Replace with API Endpoint

    Route::post('/destroy/provider', [SocialController::class, 'destroyProvider'])
         ->name('provider.destroy'); //TODO: Replace with API Endpoint

    Route::prefix('stats')->group(static function() {
        Route::get('/', [StatisticController::class, 'renderMainStats'])
             ->name('stats');
        Route::get('/stations', [StatisticController::class, 'renderStations'])
             ->name('stats.stations');
        Route::get('/daily/{dateString}', [DailyStatsController::class, 'renderDailyStats'])
             ->name('stats.daily');
    });

    Route::prefix('open-data')->group(function() {
        Route::get('/wikidata', [WikidataController::class, 'indexHelpPage'])
             ->name('open-data.wikidata');
    });

    Route::prefix('settings')->group(function() {

        Route::prefix('/applications')->group(function() {
            Route::get('/', [DevController::class, 'renderAppList'])->name('dev.apps');
            Route::post('/createPersonalAccessToken', [DevController::class, 'createPersonalAccessToken'])
                 ->name('dev.apps.createPersonalAccessToken');
            Route::get('/create', [DevController::class, 'renderCreateApp'])->name('dev.apps.create');
            Route::get('/{appId}', [DevController::class, 'renderUpdateApp'])->name('dev.apps.edit');
            Route::post('/{appId}', [DevController::class, 'updateApp'])->name('dev.apps.update');           //TODO: Replace with API Endpoint
            Route::post('/{appId}/destroy', [DevController::class, 'destroyApp'])->name('dev.apps.destroy'); //TODO: Replace with API Endpoint
            Route::post('/', [DevController::class, 'createApp'])->name('dev.apps.create.post');             //TODO: Replace with API Endpoint
        });

        Route::redirect('/', 'settings/profile')->name('settings');
        Route::get('/profile', [SettingsController::class, 'renderProfile'])->name('settings.profile');
        Route::get('/privacy', [SettingsController::class, 'renderPrivacy'])->name('settings.privacy');
        Route::post('/profile', [SettingsController::class, 'updateMainSettings']);
        Route::post('/update/privacy', [SettingsController::class, 'updatePrivacySettings'])
             ->name('settings.privacy.update');

        Route::view('/account', 'settings.account')
             ->name('settings.account');
        Route::post('/account/update', [SettingsController::class, 'updatePassword'])
             ->name('password.change');

        Route::get('/security/login-providers', [SettingsController::class, 'renderLoginProviders'])
             ->name('settings.login-providers');
        Route::get('/security/sessions', [SettingsController::class, 'renderSessions'])
             ->name('settings.sessions');

        Route::get('/security/ics', [SettingsController::class, 'renderIcs'])->name('settings.ics');
        Route::get('/security/api-tokens', [SettingsController::class, 'renderToken'])->name('settings.tokens');
        Route::get('/security/webhooks', [SettingsController::class, 'renderWebhooks'])->name('settings.webhooks');

        Route::get('/follower', [SettingsController::class, 'renderFollowerSettings'])
             ->name('settings.follower');
        Route::post('/follower/remove', [\App\Http\Controllers\SettingsController::class, 'removeFollower'])
             ->name('settings.follower.remove'); //TODO: Replace with API Endpoint
        Route::post('/follower/approve', [SettingsController::class, 'approveFollower'])
             ->name('settings.follower.approve'); //TODO: Replace with API Endpoint
        Route::post('/follower/reject', [SettingsController::class, 'rejectFollower'])
             ->name('settings.follower.reject'); //TODO: Replace with API Endpoint

        Route::get('/blocks', [SettingsController::class, 'renderBlockedUsers'])->name('settings.blocks');
        Route::get('/mutes', [SettingsController::class, 'renderMutedUsers'])->name('settings.mutes');

        Route::post('/delsession', [UserController::class, 'deleteSession'])
             ->name('delsession'); //TODO: Replace with API Endpoint
        Route::post('/deltoken', [UserController::class, 'deleteToken'])
             ->name('deltoken'); //TODO: Replace with API Endpoint
        Route::post('/delwebhook', [WebhookController::class, 'deleteWebhook'])
             ->name('delwebhook'); //TODO: Replace with API Endpoint
    });

    Route::get('/dashboard', [FrontendStatusController::class, 'getDashboard'])
         ->name('dashboard');

    Route::get('/dashboard/global', [FrontendStatusController::class, 'getGlobalDashboard'])
         ->name('globaldashboard');

    Route::post('/status/update', [StatusController::class, 'updateStatus'])
         ->name('status.update'); //TODO: Replace with API Endpoint

    Route::view('/export', 'export')->name('export');

    Route::post('/createfollow', [FrontendUserController::class, 'CreateFollow'])
         ->name('follow.create'); //TODO: Replace with API Endpoint

    Route::post('/requestfollow', [FrontendUserController::class, 'requestFollow'])
         ->name('follow.request'); //TODO: Replace with API Endpoint

    Route::post('/destroyfollow', [FrontendUserController::class, 'destroyFollow'])
         ->name('follow.destroy'); //TODO: Replace with API Endpoint

    Route::get('/transport/train/autocomplete/{station}', [FrontendTransportController::class, 'TrainAutocomplete'])
         ->name('transport.train.autocomplete');

    Route::get('/stationboard', [VueFrontendController::class, 'stationboard'])->name('stationboard');

    Route::redirect('/trains/stationboard', '/stationboard')->name('trains.stationboard');

    Route::get('/search/', [FrontendUserController::class, 'searchUser'])->name('userSearch');

    Route::post('/user/block', [\App\Http\Controllers\Frontend\UserController::class, 'blockUser'])
         ->name('user.block'); //TODO: Replace with API Endpoint
    Route::post('/user/unblock', [\App\Http\Controllers\Frontend\UserController::class, 'unblockUser'])
         ->name('user.unblock'); //TODO: Replace with API Endpoint
    Route::post('/user/mute', [\App\Http\Controllers\Frontend\UserController::class, 'muteUser'])
         ->name('user.mute'); //TODO: Replace with API Endpoint
    Route::post('/user/unmute', [\App\Http\Controllers\Frontend\UserController::class, 'unmuteUser'])
         ->name('user.unmute'); //TODO: Replace with API Endpoint
});

Route::get('/sitemap.xml', [SitemapController::class, 'renderSitemap']);

Route::get('/.well-known/webfinger', [WebFingerController::class, 'endpoint']);
