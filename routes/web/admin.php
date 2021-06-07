<?php

use App\Http\Controllers\Frontend\Admin\DashboardController;
use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'userrole:5'])->group(function() {
    Route::get('/', [DashboardController::class, 'renderDashboard'])
         ->name('admin.dashboard');

    Route::get('/events', [AdminEventController::class, 'renderList'])
         ->name('admin.events');
    Route::post('/events/delete', [AdminEventController::class, 'deleteEvent'])
         ->name('admin.events.delete');

    Route::get('/events/suggestions', [AdminEventController::class, 'renderSuggestions'])
         ->name('admin.events.suggestions');
    Route::get('/events/suggestions/accept/{id}', [AdminEventController::class, 'renderSuggestionCreation'])
         ->name('admin.events.suggestions.accept');
    Route::post('/events/suggestions/deny', [AdminEventController::class, 'denySuggestion'])
         ->name('admin.events.suggestions.deny');
    Route::post('/events/suggestions/accept', [AdminEventController::class, 'acceptSuggestion'])
         ->name('admin.events.suggestions.accept.do');


    Route::get('/events/create', [AdminEventController::class, 'renderCreate'])
         ->name('admin.events.create');
    Route::post('/events/create', [AdminEventController::class, 'create']);

    Route::get('/events/edit/{id}', [AdminEventController::class, 'renderEdit'])
         ->name('admin.events.edit');
    Route::post('/events/edit/{id}', [AdminEventController::class, 'edit']);

});
