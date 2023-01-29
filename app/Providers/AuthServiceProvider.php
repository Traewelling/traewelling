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

    public static array $scopes = [
        'read-statuses'                  => 'see all statuses',
        'read-notifications'             => 'see your notifications',
        'read-statistics'                => 'see your statistics',
        'write-statuses'                 => 'create, edit, delete statuses',
        'write-likes'                    => 'create and remove likes',
        'write-notifications'            => 'mark notifications as read, clear notifications',
        'write-exports'                  => 'request data exports',
        'write-follows'                  => 'follow and unfollow users',
        'write-followers'                => 'accept follow requests and remove followers',
        'write-blocks'                   => 'block and unblock users, mute and unmute users',
        'write-event-suggestions'        => 'suggest events in your name',
        'write-support-tickets'          => 'create support tickets in your name',
        'settings-read'                  => 'see your settings, email, etc.',
        'settings-write-profile'         => 'edit your profile',
        'settings-read-profile'          => 'see your profile data, e.g. email',
        'settings-write-mail'            => 'edit your email',
        'settings-write-profile-picture' => 'edit your profile picture',
        'settings-write-privacy'         => 'change your privacy settings',
        'settings-read-followers'        => 'show follow-requests and followers',
        'settings-write-calendar'        => 'create and delete new calendar-tokens',
        'extra-write-password'           => 'change your password',
        'extra-terminate-sessions'       => 'log you out of other sessions and apps',
        'extra-delete'                   => 'delete your account'
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void {
        $this->registerPolicies();
        Passport::tokensCan(self::$scopes);
        Passport::setDefaultScope([
                                      'read-statuses',
                                      'write-statuses',
                                      'read-notifications',
                                      'write-follows',
                                      'write-blocks',
                                      'write-notifications'
                                  ]);
    }
}
