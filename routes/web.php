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
use App\Http\Controllers\Frontend\DebugController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\IcsController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Frontend\LeaderboardController;
use App\Http\Controllers\Frontend\SettingsController;
use App\Http\Controllers\Frontend\Social\MastodonController;
use App\Http\Controllers\Frontend\Stats\DailyStatsController;
use App\Http\Controllers\Frontend\Transport\StatusController;
use App\Http\Controllers\Frontend\User\ProfilePictureController;
use App\Http\Controllers\Frontend\UserController;
use App\Http\Controllers\Frontend\VueFrontendController;
use App\Http\Controllers\Frontend\WebFingerController;
use App\Http\Controllers\FrontendStatusController;
use App\Http\Controllers\FrontendUserController;
use App\Http\Controllers\PrivacyAgreementController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(base_path('routes/web/admin.php'));

Route::get('/@{username}/picture', [ProfilePictureController::class, 'generateProfilePicture'])
    ->name('profile.picture');

Route::get('/', [LandingPageController::class, 'renderLandingPage'])
    ->name('static.welcome');

Route::permanentRedirect('/about', 'https://help.traewelling.de/faq/');

Route::prefix('legal')->group(function () {
    Route::view('/', 'legal.notice')
        ->name('legal.notice');
    Route::get('/privacy-policy', [PrivacyAgreementController::class, 'intercept'])->name('legal.privacy');
    Route::get('/privacy-policy/{id}', [PrivacyAgreementController::class, 'intercept']);
});

Route::get('/@{username}', [FrontendUserController::class, 'getProfilePage'])
    ->name('profile');

Route::view('/leaderboard', 'vue.leaderboard')
    ->name('leaderboard');

Route::get('/leaderboard/{date}', [LeaderboardController::class, 'renderMonthlyLeaderboard'])
    ->name('leaderboard.month');

Route::view('/statuses/active', 'vue.active-journeys')
    ->name('statuses.active');

Route::get('/event/{slug}', [FrontendStatusController::class, 'statusesByEvent'])
    ->name('event');

Route::get('/events', [EventController::class, 'renderEventOverview'])
    ->name('events');

Route::get('/changelog', [ChangelogController::class, 'renderChangelog'])
    ->name('changelog');

Auth::routes(['verify' => true, 'register' => config('app.registration.enabled')]);

Route::get('/auth/redirect/mastodon', [MastodonController::class, 'redirect']);
Route::get('/callback/mastodon', [MastodonController::class, 'callback']);

Route::get('/status/{id}', [FrontendStatusController::class, 'getStatus'])
    ->whereNumber('id')
    ->name('status');

/**
 * These routes can be used by logged in users although they have not signed the privacy policy yet.
 */
Route::middleware(['auth'])->group(function () {
    Route::personalDataExports('personal-data-exports');

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
Route::middleware(['auth', 'privacy'])->group(function () {

    Route::get('/year-in-review', function () {
        return view('layouts.year-in-review');
    });

    Route::view('/trip/create', 'beta.trip-creation')
        ->name('trip.create');

    Route::prefix('stats')->group(static function () {
        Route::permanentRedirect('/', '/statistics');
        Route::permanentRedirect('/daily/{dateString}', '/statistics/daily/{dateString}');
    });

    Route::prefix('statistics')->group(static function () {
        Route::get('/', [VueFrontendController::class, 'statsDashboard'])
            ->name('stats');
        Route::get('/daily/{dateString}', [DailyStatsController::class, 'renderDailyStats'])
            ->name('stats.daily');
    });

    Route::prefix('contribute')->group(function () {
        Route::get('/{any?}', function () {
            return view('vue.spa');
        })->where('any', '.*')->name('contribute');
    });

    Route::prefix('settings')->group(function () {

        Route::prefix('/applications')->group(function () {
            Route::get('/{any?}', fn () => view('vue.spa'))->where('any', '.*');
        });

        Route::redirect('/', '/settings/profile')->name('settings');
        Route::view('/profile', 'vue.spa')->name('settings.profile');
        Route::view('/privacy', 'vue.spa')->name('settings.privacy');

        Route::view('/account', 'settings.account')
            ->name('settings.account');
        Route::post('/account/update', [SettingsController::class, 'updatePassword'])
            ->name('password.change');

        Route::view('/blocks', 'vue.spa')->name('settings.blocks');
        Route::view('/mutes', 'vue.spa')->name('settings.mutes');

        Route::get('/{any?}', function () {
            return view('vue.spa');
        })->where('any', '.*')->name('settings.vue');
    });

    Route::view('/dashboard', 'vue.dashboard')
        ->name('dashboard');

    Route::post('/status/update', [StatusController::class, 'updateStatus'])
        ->name('status.update'); // TODO: Replace with API Endpoint

    Route::view('/export', 'export')->name('export');

    Route::view('/tickets', 'vue.tickets')->name('tickets');
    Route::view('/tickets/{id}', 'vue.ticket-detail')->name('tickets.detail');

    Route::get('/stationboard', [VueFrontendController::class, 'stationBoard'])->name('stationboard');

    Route::redirect('/trains/stationboard', '/stationboard')->name('trains.stationboard');

    Route::get('/search/', [FrontendUserController::class, 'searchUser'])->name('userSearch');

    Route::post('/user/unblock', [UserController::class, 'unblockUser'])
        ->name('user.unblock'); // TODO: Replace with API Endpoint
    Route::post('/user/unmute', [UserController::class, 'unmuteUser'])
        ->name('user.unmute'); // TODO: Replace with API Endpoint
});

Route::get('/sitemap.xml', [SitemapController::class, 'renderSitemap']);

Route::get('/.well-known/webfinger', [WebFingerController::class, 'endpoint']);

Route::prefix('debug')->group(function () {
    // routes for debugging purposes and to show users which data is used by current instance
    Route::get('/motis-sources', [DebugController::class, 'showMotisSources']);
    Route::get('/stations', [DebugController::class, 'showStationMap']);
});
