<?php

namespace App\Providers;

use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use App\Policies\FollowPolicy;
use App\Policies\StatusPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

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
        $this->registerPolicies();

        Passport::tokensCan([
                                'read'                => 'see most of your accounts data except settings',
                                'read-statuses'       => 'see all statuses',
                                'read-notifications'  => 'see your notifications',
                                'read-statistics'     => 'see your statistics',
                                'write'               => 'write statuses, follow users, etc',
                                'write-statuses'      => 'create, edit, delete statuses',
                                'write-notifications' => 'mark notifications as read, clear notifications',
                                'write-exports'       => 'request data exports',
                                'write-follows'       => 'follow and unfollow users',
                                'write-blocks'        => 'block and unblock users, mute and unmute users',
                                'settings-read'       => 'see your settings, email, etc.',
                                'settings-'
                                'settings-write-profile' => 'edit your profile',
                                'settings-read-profile'  => 'see your profile data, e.g. email',
                                'settings-write-mail'    => 'edit your email',
                                'settings-'
                            ]);
    }
}
