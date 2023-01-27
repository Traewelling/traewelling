<?php

namespace App\Providers;

use App\Http\Controllers\Backend\Auth\AccessTokenController;
use App\Http\Controllers\Backend\Auth\AuthorizationController;
use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use App\Policies\FollowPolicy;
use App\Policies\StatusPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Status::class => StatusPolicy::class,
        User::class   => UserPolicy::class,
        Follow::class => FollowPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Override passport routes
        Route::group(['prefix' => 'oauth', 'as' => 'oauth.'], function () {
            Route::get('authorize', [AuthorizationController::class, 'authorize'])
                ->middleware(['web'])
                ->name('authorizations.authorize');
            Route::post("/token", [AccessTokenController::class, 'issueToken'])
                ->middleware("throttle")
                ->name("authorizations.token");
        });
    }
}
