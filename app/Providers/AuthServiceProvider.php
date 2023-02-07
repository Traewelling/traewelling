<?php

namespace App\Providers;

use App\Http\Controllers\Backend\Auth\AccessTokenController;
use App\Http\Controllers\Backend\Auth\ApproveAuthorizationController;
use App\Http\Controllers\Backend\Auth\AuthorizationController;
use App\Models\Follow;
use App\Models\OAuthClient;
use App\Models\Status;
use App\Models\User;
use App\Policies\FollowPolicy;
use App\Policies\StatusPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider {
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
    public function boot(): void {
        $this->registerPolicies();

        Passport::useClientModel(OAuthClient::class);

        // Override passport routes
        Route::group(['prefix' => 'oauth', 'as' => 'oauth.'], function () {
            Route::get('authorize', [AuthorizationController::class, 'authorize'])
                ->middleware(['web'])
                ->name('authorizations.authorize');
            Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])
                ->middleware(['web'])
                ->name('authorizations.approve');
            Route::post("/token", [AccessTokenController::class, 'issueToken'])
                ->middleware("throttle")
                ->name("authorizations.token");
        });
    }
}
