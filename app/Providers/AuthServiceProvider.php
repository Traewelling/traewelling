<?php

namespace App\Providers;

use App\Http\Controllers\Backend\Auth\AccessTokenController;
use App\Http\Controllers\Backend\Auth\ApproveAuthorizationController;
use App\Http\Controllers\Backend\Auth\AuthorizationController;
use App\Models\Follow;
use App\Models\Like;
use App\Models\OAuthClient;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\User;
use App\Models\Webhook;
use App\Policies\FollowPolicy;
use App\Policies\LikePolicy;
use App\Policies\StatusPolicy;
use App\Policies\StatusTagPolicy;
use App\Policies\TokenPolicy;
use App\Policies\UserPolicy;
use App\Policies\WebhookPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Status::class    => StatusPolicy::class,
        User::class      => UserPolicy::class,
        Follow::class    => FollowPolicy::class,
        Like::class      => LikePolicy::class,
        Webhook::class   => WebhookPolicy::class,
        StatusTag::class => StatusTagPolicy::class,
        Token::class     => TokenPolicy::class,
    ];

    //ToDo Translate
    public static array $scopes = [
        'read-statuses'                  => 'see all statuses',
        'read-notifications'             => 'see your notifications',
        'read-statistics'                => 'see your statistics',
        'read-search'                    => 'search in Träwelling',
        'write-statuses'                 => 'create, edit, delete statuses',
        'write-likes'                    => 'create and remove likes',
        'write-notifications'            => 'mark notifications as read, clear notifications',
        'write-exports'                  => 'request data exports',
        'write-follows'                  => 'follow and unfollow users',
        'write-followers'                => 'accept follow requests and remove followers',
        'write-blocks'                   => 'block and unblock users, mute and unmute users',
        'write-event-suggestions'        => 'suggest events in your name',
        'write-support-tickets'          => 'create support tickets in your name',
        'read-settings'                  => 'see your settings, email, etc.',
        'write-settings-profile'         => 'edit your profile',
        'read-settings-profile'          => 'see your profile data, e.g. email',
        'write-settings-mail'            => 'edit your email',
        'write-settings-profile-picture' => 'edit your profile picture',
        'write-settings-privacy'         => 'change your privacy settings',
        'read-settings-followers'        => 'show follow-requests and followers',
        'write-settings-calendar'        => 'create and delete new calendar-tokens',
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
        Passport::useClientModel(OAuthClient::class);

        // Override passport routes
        Route::group(['prefix' => 'oauth', 'as' => 'oauth.'], function() {
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
        Passport::tokensCan(self::$scopes);
        Passport::setDefaultScope([
                                      'read-statuses',
                                      'read-statistics',
                                      'write-statuses',
                                      'write-likes',
                                      'read-notifications',
                                      'write-notifications',
                                      'write-follows',
                                      'write-followers',
                                      'write-blocks',
                                  ]);
    }
}
