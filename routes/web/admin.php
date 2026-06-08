<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'permission:view-backend'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', fn () => view('admin.app'))->name('admin.users');
            Route::get('/{id}', fn () => view('admin.app'))->name('admin.users.show');
        });

        Route::get('trips', fn () => view('admin.app'))
            ->name('admin.trips');

        Route::get('operators', fn () => view('admin.app'))
            ->name('admin.operators');

        Route::get('activity', fn () => view('admin.app'))
            ->name('admin.activity');
    });

    // Welcome page: accessible to all backend users (admins + event-moderators (legacy until contributing system))
    Route::get('/', fn () => view('admin.app'));

    Route::middleware('permission:view-events|accept-events|deny-events|create-events|update-events|delete-events')
        ->group(function () {
            Route::get('events/{any?}', fn () => view('admin.app'))->where('any', '.*');
            Route::get('event-suggestions/{any?}', fn () => view('admin.app'))->where('any', '.*');
        });

    // Catch-all for admin-only SPA pages. must be last so specific routes take precedence.
    // Vue Router handles routing within the app; add new pages to admin-routes.ts.
    Route::middleware('role:admin')
        ->get('{any}', fn () => view('admin.app'))
        ->where('any', '.+');
});
