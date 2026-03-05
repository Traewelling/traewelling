<?php

use App\Http\Controllers\Backend\Admin\AlertController;
use App\Http\Controllers\Frontend\Admin\ActivityController;
use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use App\Http\Controllers\Frontend\Admin\LicensesController;
use App\Http\Controllers\Frontend\Admin\MotisSourceController;
use App\Http\Controllers\Frontend\Admin\OperatorController;
use App\Http\Controllers\Frontend\Admin\ReportController;
use App\Http\Controllers\Frontend\Admin\RouteSegmentController;
use App\Http\Controllers\Frontend\Admin\StationController;
use App\Http\Controllers\Frontend\Admin\StatusEditController;
use App\Http\Controllers\Frontend\Admin\TripController;
use App\Http\Controllers\Frontend\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-backend'])->group(function () {
    Route::view('/', 'admin.welcome') // attention: route accessible for admins and event-moderators!
        ->name('admin.welcome');

    Route::middleware('role:admin')->group(function () {
        // these routes are only accessible for admins
        Route::prefix('sources')->group(function () {
            Route::get('/', [MotisSourceController::class, 'index'])
                ->name('admin.sources');
            Route::post('/', [MotisSourceController::class, 'show'])->name('admin.sources.show');
            Route::post('/mass-assign', [MotisSourceController::class, 'massAssign'])
                ->name('admin.sources.mass-assign');
        });
        Route::prefix('alerts')->group(function () {
            Route::get('/', [AlertController::class, 'index'])
                ->name('admin.alerts');
            Route::post('/delete', [AlertController::class, 'destroy'])
                ->name('admin.alerts.destroy');
            Route::get('/create', [AlertController::class, 'create'])
                ->name('admin.alerts.store');
            Route::post('/create', [AlertController::class, 'store'])
                ->name('admin.alerts.create');
            Route::get('/{id}/edit', [AlertController::class, 'edit'])
                ->name('admin.alerts.edit');
            Route::post('/{id}/edit', [AlertController::class, 'update'])
                ->name('admin.alerts.update');
        });
        Route::resource('licenses', LicensesController::class)
            ->only(['create', 'store', 'index']);

        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])
                ->name('admin.reports');
            Route::get('/{id}', [ReportController::class, 'showReport'])
                ->name('admin.reports.show');
        });

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

        Route::prefix('statuses')->group(function () {
            Route::get('/', [StatusEditController::class, 'index'])
                ->name('admin.statuses');
            Route::get('/find', [StatusEditController::class, 'find'])
                ->name('admin.statuses.find');
            Route::get('/{statusId}/edit', [StatusEditController::class, 'renderEdit'])
                ->name('admin.statuses.edit');
            Route::post('/{statusId}/edit', [StatusEditController::class, 'edit']);

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
            Route::get('/', [StationController::class, 'index'])
                ->name('admin.stations');

            Route::get('/{id}', [StationController::class, 'show'])
                ->name('admin.station');

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
});
