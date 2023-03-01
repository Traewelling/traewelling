<?php

namespace App\Providers;

use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use App\Policies\FollowPolicy;
use App\Policies\StatusPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
    public function boot(): void {
        //
    }
}
