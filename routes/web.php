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

use App\Http\Controllers\Frontend\IcsController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\Frontend\LeaderboardController;
use App\Http\Controllers\Frontend\Social\MastodonController;
use App\Http\Controllers\Frontend\User\ProfilePictureController;
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

Route::prefix('legal')->group(function () {
    Route::get('/', fn () => view('vue.spa', [
        'title' => __('menu.legal-notice'),
        'robots' => 'noindex',
    ]))->name('legal.notice');
    Route::get('/privacy-policy', fn () => view('vue.spa', [
        'title' => __('privacy.title'),
        'robots' => 'noindex',
    ]))->name('legal.privacy');
});

Route::get('/@{username}', [FrontendUserController::class, 'getProfilePage'])
    ->name('profile');

Route::view('/leaderboard', 'vue.spa')
    ->name('leaderboard');

Route::get('/leaderboard/{date}', [LeaderboardController::class, 'redirectToMonthlyLeaderboard'])
    ->name('leaderboard.month');

Route::get('/leaderboard/monthly/{month}', [LeaderboardController::class, 'renderMonthlyLeaderboard'])
    ->name('leaderboard.monthly');

Route::get('/statuses/active', fn () => view('vue.spa', [
    'title' => __('menu.active'),
    'robots' => 'index',
    'description' => __('description.en-route'),
]))->name('statuses.active');

Route::get('/event/{slug}', [FrontendStatusController::class, 'statusesByEvent'])
    ->name('event');

Route::get('/events', fn () => view('vue.spa', [
    'title' => __('events.live'),
    'canonical' => route('events'),
    'robots' => 'index',
]))->name('events');

Route::get('/changelog', fn () => view('vue.spa', [
    'title' => __('changelog'),
    'canonical' => route('changelog'),
]))->name('changelog');

Auth::routes(['verify' => true, 'register' => config('app.registration.enabled')]);

Route::get('/auth/redirect/mastodon', [MastodonController::class, 'redirect']);
Route::get('/callback/mastodon', [MastodonController::class, 'callback']);

Route::get('/status/{uuid}', [FrontendStatusController::class, 'getStatus'])
    ->whereUuid('uuid')
    ->name('status');

Route::get('/status/{id}', [FrontendStatusController::class, 'redirectToStatus'])
    ->whereNumber('id');

/**
 * These routes can be used by logged in users although they have not signed the privacy policy yet.
 */
Route::middleware(['auth'])->group(function () {
    Route::personalDataExports('personal-data-exports');

    Route::view('/gdpr-intercept', 'vue.spa')
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

    Route::permanentRedirect('/stats', '/statistics');
    Route::permanentRedirect('/stats/daily/{dateString}', '/statistics/daily/{dateString}');
    Route::redirect('/trains/stationboard', '/stationboard');

    Route::view('/dashboard', 'vue.spa')
        ->name('dashboard');

    Route::view('/status/{uuid}/change-exit', 'vue.spa')->whereUuid('uuid');

    Route::view('/{page}', 'vue.spa')
        ->where('page', '(checkin|contribute|debug|export|line-run|notifications|route-map|search|settings|stationboard|statistics|tickets|trip|trips)(/.*)?');
});

Route::get('/sitemap.xml', [SitemapController::class, 'renderSitemap']);

Route::get('/.well-known/webfinger', [WebFingerController::class, 'endpoint']);

Route::fallback(fn () => abort(404))
    ->where('fallbackPlaceholder', '(?!api/).*');
