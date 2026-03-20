<?php

use App\Http\Controllers\Frontend\Admin\ActivityController;
use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use App\Http\Controllers\Frontend\Admin\LicensesController;
use App\Http\Controllers\Frontend\Admin\MotisSourceController;
use App\Http\Controllers\Frontend\Admin\OperatorController;
use App\Http\Controllers\Frontend\Admin\RouteSegmentController;
use App\Http\Controllers\Frontend\Admin\StationController;
use App\Http\Controllers\Frontend\Admin\TripController;
use App\Http\Controllers\Frontend\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-backend'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        // these routes are only accessible for admins
        Route::prefix('sources')->group(function () {
            Route::get('/', [MotisSourceController::class, 'index'])
                ->name('admin.sources');
            Route::post('/', [MotisSourceController::class, 'show'])->name('admin.sources.show');
            Route::post('/mass-assign', [MotisSourceController::class, 'massAssign'])
                ->name('admin.sources.mass-assign');
        });
        Route::resource('licenses', LicensesController::class)
            ->only(['create', 'store', 'index']);

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'renderIndex'])
                ->name('admin.users');
            Route::get('/{id}', [UserController::class, 'renderUser'])
                ->name('admin.users.show');
            Route::post('/', [UserController::class, 'updateRoles'])
                ->name('admin.users.update-roles');
            Route::post('/update-mail', [UserController::class, 'updateMail'])
                ->name('admin.users.update-mail');
        });

        Route::prefix('trips')->group(function () {
            Route::get('/', [TripController::class, 'index'])
                ->name('admin.trips');
            Route::get('/{id}', [TripController::class, 'renderTrip'])
                ->whereNumber('id')
                ->name('admin.trips.show');
            Route::get('/{id}/reroute', [TripController::class, 'rerouteTrip'])
                ->whereNumber('id')
                ->name('admin.trips.reroute');
        });

        Route::prefix('routesegment')->group(function () {
            Route::get('/{id}', [RouteSegmentController::class, 'renderSegment'])
                ->name('admin.routesegment.show');
            // experimental endpoint: Should be into the contribution-system mid-term:
            Route::post('/{id}/brouter-preview', [RouteSegmentController::class, 'brouterPreview'])
                ->name('admin.routesegment.brouter-preview');
            // experimental endpoint: Should be into the contribution-system mid-term:
            Route::post('/{id}/save-from-brouter', [RouteSegmentController::class, 'saveFromBrouter'])
                ->name('admin.routesegment.save-from-brouter');
        });

        Route::prefix('stations')->group(function () {
            Route::post('/wikidata/import', [StationController::class, 'importWikidata'])->name('backend.status.import.wikidata'); // TODO: Make this an API endpoint when it is accessible for users too
            Route::post('/{id}/wikidata', [StationController::class, 'fetchWikidata']);
        });

        Route::prefix('operators')->group(function () {
            Route::get('/', [OperatorController::class, 'index'])
                ->name('admin.operators');
        });

        Route::get('activity', [ActivityController::class, 'render'])
            ->name('admin.activity');
    });

    // Welcome page: accessible to all backend users (admins + event-moderators (legacy until contributing system))
    Route::get('/', fn () => view('admin.app'));

    Route::prefix('events')
        ->middleware(['permission:view-events|accept-events|deny-events|update-events|delete-events'])
        ->group(function () {
            // these routes are also accessible for event-moderators - attention here - don't expose too much!

            Route::get('/', [AdminEventController::class, 'index'])
                ->name('admin.events');
            Route::post('/delete', [AdminEventController::class, 'deleteEvent'])
                ->middleware('permission:delete-events')
                ->name('admin.events.delete');

            Route::get('/suggestions', [AdminEventController::class, 'renderSuggestions'])
                ->middleware('permission:accept-events|deny-events')
                ->name('admin.events.suggestions');
            Route::get('/suggestions/accept/{id}', [AdminEventController::class, 'renderSuggestionCreation'])
                ->middleware('permission:accept-events')
                ->name('admin.events.suggestions.accept');
            Route::post('/suggestions/deny', [AdminEventController::class, 'denySuggestion'])
                // ->middleware('can:deny-events') - TODO: working in the browser, but not in the tests
                ->name('admin.events.suggestions.deny');
            Route::post('/suggestions/accept', [AdminEventController::class, 'acceptSuggestion'])
                // ->middleware(['can:accept-events']) - TODO: working in the browser, but not in the tests
                ->name('admin.events.suggestions.accept.do');

            Route::view('/create', 'admin.events.form')
                ->middleware('permission:create-events')
                ->name('admin.events.create');
            Route::post('/create', [AdminEventController::class, 'create'])
                ->middleware('permission:create-events');

            Route::get('/edit/{id}', [AdminEventController::class, 'renderEdit'])
                ->middleware('permission:update-events')
                ->name('admin.events.edit');
            Route::post('/edit/{id}', [AdminEventController::class, 'edit'])
                ->middleware('permission:update-events');
        });

    // Catch-all for admin-only SPA pages. must be last so specific routes take precedence.
    // Vue Router handles routing within the app; add new pages to admin-routes.ts.
    Route::middleware('role:admin')
        ->get('{any}', fn () => view('admin.app'))
        ->where('any', '.+');
});
