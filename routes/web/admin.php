<?php

use App\Http\Controllers\Frontend\Admin\CheckinController;
use App\Http\Controllers\Frontend\Admin\DashboardController;
use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use App\Http\Controllers\Frontend\Admin\LocationController;
use App\Http\Controllers\Frontend\Admin\StatusEditController;
use App\Http\Controllers\Frontend\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'userrole:5'])->group(function() {
    Route::get('/', [DashboardController::class, 'renderDashboard'])
         ->name('admin.dashboard');

    Route::prefix('checkin')->group(function() {
        Route::get('/', [CheckinController::class, 'renderStationboard'])
             ->name('admin.stationboard');
        Route::get('/trip/{tripId}', [CheckinController::class, 'renderTrip'])
             ->name('admin.trip');
        Route::post('/checkin', [CheckinController::class, 'checkin'])
             ->name('admin.checkin');
    });

    Route::prefix('locations')->group(function() {
        Route::get('/', [LocationController::class, 'index'])
             ->name('admin.locations');

        Route::get('/edit/{id}', [LocationController::class, 'renderEdit'])
             ->name('admin.locations.edit');
        Route::post('/edit/{id}', [LocationController::class, 'edit']);

        Route::view('/create', 'admin.locations.edit')
             ->name('admin.locations.create');
        Route::post('/create', [LocationController::class, 'create']);

        Route::post('/delete', [LocationController::class, 'delete'])
             ->name('admin.locations.delete');
    });

    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'renderIndex'])
             ->name('admin.users');
    });

    Route::prefix('status')->group(function() {
        Route::get('/', [StatusEditController::class, 'renderMain'])
             ->name('admin.status');
        Route::get('/edit', [StatusEditController::class, 'renderEdit'])
             ->name('admin.status.edit');
        Route::post('/edit', [StatusEditController::class, 'edit']);
    });

    Route::prefix('events')->group(function() {
        Route::get('/', [AdminEventController::class, 'renderList'])
             ->name('admin.events');
        Route::post('/delete', [AdminEventController::class, 'deleteEvent'])
             ->name('admin.events.delete');

        Route::get('/suggestions', [AdminEventController::class, 'renderSuggestions'])
             ->name('admin.events.suggestions');
        Route::get('/suggestions/accept/{id}', [AdminEventController::class, 'renderSuggestionCreation'])
             ->name('admin.events.suggestions.accept');
        Route::post('/suggestions/deny', [AdminEventController::class, 'denySuggestion'])
             ->name('admin.events.suggestions.deny');
        Route::post('/suggestions/accept', [AdminEventController::class, 'acceptSuggestion'])
             ->name('admin.events.suggestions.accept.do');


        Route::get('/create', [AdminEventController::class, 'renderCreate'])
             ->name('admin.events.create');
        Route::post('/create', [AdminEventController::class, 'create']);

        Route::get('/edit/{id}', [AdminEventController::class, 'renderEdit'])
             ->name('admin.events.edit');
        Route::post('/edit/{id}', [AdminEventController::class, 'edit']);
    });
});
