<?php
/**
 * Routes for the admins.
 */

use App\Http\Controllers\Frontend\Admin\EventController as AdminEventController;
use App\Http\Controllers\FrontendEventController;
use App\Http\Controllers\FrontendStatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'userrole:5'])->group(function() {
    Route::get('/', [FrontendStatusController::class, 'usageboard'])
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


    Route::get('/events/new', [FrontendEventController::class, 'newForm'])
         ->name('events.newform');

    Route::post('/events/new', [FrontendEventController::class, 'store'])
         ->name('events.store');

    Route::get('/events/{slug}/delete', [FrontendEventController::class, 'destroy'])
         ->name('events.delete');

    Route::get('/events/{slug}', [FrontendEventController::class, 'show'])
         ->name('events.show');

    Route::put('/events/{slug}', [FrontendEventController::class, 'update'])
         ->name('events.update');
});