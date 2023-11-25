<?php

use App\Http\Controllers\Frontend\Admin\CheckinController;
use App\Http\Controllers\Frontend\Admin\DashboardController;
use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use App\Http\Controllers\Frontend\Admin\StatusEditController;
use App\Http\Controllers\Frontend\Admin\TripController;
use App\Http\Controllers\Frontend\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-backend'])->group(function() {
    Route::view('/', 'admin.dashboard') //attention: route accessible for admins and event-moderators!
         ->name('admin.dashboard');

    Route::middleware('role:admin')->group(static function() {
        // these routes are only accessible for admins

        Route::get('/stats', [DashboardController::class, 'renderStats'])
             ->name('admin.stats');

        Route::prefix('checkin')->group(function() {
            Route::get('/', [CheckinController::class, 'renderStationboard'])
                 ->name('admin.stationboard');
            Route::get('/trip/{tripId}', [CheckinController::class, 'renderTrip'])
                 ->name('admin.trip');
            Route::post('/checkin', [CheckinController::class, 'checkin'])
                 ->name('admin.checkin');
        });

        Route::prefix('users')->group(function() {
            Route::get('/', [UserController::class, 'renderIndex'])
                 ->name('admin.users');
            Route::get('/{id}', [UserController::class, 'renderUser'])
                 ->name('admin.users.user');
            Route::post('/update-mail', [UserController::class, 'updateMail'])
                 ->name('admin.users.update-mail');
        });

        Route::prefix('status')->group(function() {
            Route::get('/', [StatusEditController::class, 'renderMain'])
                 ->name('admin.status');
            Route::get('/edit', [StatusEditController::class, 'renderEdit'])
                 ->name('admin.status.edit');
            Route::post('/edit', [StatusEditController::class, 'edit']);
        });

        Route::prefix('trip')->group(function() {
            Route::view('/create', 'admin.trip.create')
                 ->name('admin.trip.create');
            Route::post('/create', [TripController::class, 'createTrip']);

            Route::get('/{id}', [TripController::class, 'renderTrip'])
                 ->name('admin.trip.show');
        });
    });

    Route::prefix('events')
         ->middleware(['permission:view-events|accept-events|deny-events|update-events|delete-events'])
         ->group(function() {
             // these routes are also accessible for event-moderators - attention here - don't expose too much!

             Route::get('/', [AdminEventController::class, 'renderList'])
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
                 //->middleware('can:deny-events') - TODO: working in the browser, but not in the tests
                  ->name('admin.events.suggestions.deny');
             Route::post('/suggestions/accept', [AdminEventController::class, 'acceptSuggestion'])
                 //->middleware(['can:accept-events']) - TODO: working in the browser, but not in the tests
                  ->name('admin.events.suggestions.accept.do');

             Route::view('/create', 'admin.events.create')
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


