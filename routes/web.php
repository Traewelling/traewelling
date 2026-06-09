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

use App\Http\Controllers\Frontend\ChangelogController;
use App\Http\Controllers\Frontend\DebugController;
use App\Http\Controllers\Frontend\EventController;
use App\Http\Controllers\Frontend\IcsController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Frontend\LeaderboardController;
use App\Http\Controllers\Frontend\Social\MastodonController;
use App\Http\Controllers\Frontend\Stats\DailyStatsController;
use App\Http\Controllers\Frontend\Transport\StatusController;
use App\Http\Controllers\Frontend\User\ProfilePictureController;
use App\Http\Controllers\Frontend\VueFrontendController;
use App\Http\Controllers\Frontend\WebFingerController;
use App\Http\Controllers\FrontendStatusController;
use App\Http\Controllers\FrontendUserController;
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
    Route::view('/privacy-policy/{id?}', 'legal.privacy-interception')->name('legal.privacy');
});

Route::get('/@{username}', [FrontendUserController::class, 'getProfilePage'])
    ->name('profile');

Route::get('/embed/profile/{username}', [FrontendUserController::class, 'getProfilePage'])
    ->name('embed.profile');

Route::view('/leaderboard', 'vue.leaderboard')
    ->name('leaderboard');

Route::get('/leaderboard/{date}', [LeaderboardController::class, 'renderMonthlyLeaderboard'])
    ->name('leaderboard.month');

Route::get('/leaderboard/monthly/{month}', [LeaderboardController::class, 'renderBetaMonthlyLeaderboard'])
    ->name('leaderboard.monthly');

Route::view('/statuses/active', 'vue.active-journeys')
    ->name('statuses.active');

Route::get('/event/{slug}', [FrontendStatusController::class, 'statusesByEvent'])
    ->name('event');

Route::get('/embed/event/{slug}', [FrontendStatusController::class, 'statusesByEvent'])
    ->name('embed.event');

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

    Route::view('/gdpr-intercept', 'legal.privacy-interception')
        ->name('gdpr.intercept');

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
        Route::get('/{any?}', fn () => view('vue.spa'))->where('any', '.*');
    });

    Route::view('/notifications', 'vue.spa')->name('notifications');

    Route::view('/dashboard', 'vue.dashboard')
        ->name('dashboard');

    Route::get('/statuses/duplicates', fn () => appLayout() === 'layouts.tailwind-vue-layout'
        ? view('vue.spa')
        : view('vue.duplicate-checkins')
    )->name('statuses.duplicates'); // TODO: remove after 2026-05-31

    Route::post('/status/update', [StatusController::class, 'updateStatus'])
        ->name('status.update'); // TODO: Replace with API Endpoint

    Route::view('/export', 'export')->name('export');
    Route::view('/embed/export', 'export')->name('embed.export');

    Route::view('/tickets', 'vue.spa')->name('tickets');
    Route::view('/tickets/{id}', 'vue.spa')->name('tickets.detail');

    Route::get('/stationboard', [VueFrontendController::class, 'stationBoard'])->name('stationboard');
    Route::get('/embed/stationboard', [VueFrontendController::class, 'stationBoard'])->name('embed.stationboard');

    Route::redirect('/trains/stationboard', '/stationboard')->name('trains.stationboard');

    Route::get('/search/', [FrontendUserController::class, 'searchUser'])->name('userSearch');
});

Route::get('/sitemap.xml', [SitemapController::class, 'renderSitemap']);

Route::get('/.well-known/webfinger', [WebFingerController::class, 'endpoint']);

Route::prefix('debug')->group(function () {
    // routes for debugging purposes and to show users which data is used by current instance
    Route::get('/motis-sources', [DebugController::class, 'showMotisSources']);
    Route::get('/stations', [DebugController::class, 'showStationMap']);
});
