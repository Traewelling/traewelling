<?php

namespace App\Providers;

use App\Events\UserCheckedIn;
use App\Listeners\SendStatusWebhook;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class    => [
            //SendEmailVerificationNotification::class,
        ],
        UserCheckedIn::class => [
            SendStatusWebhook::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void {
        parent::boot();
    }
}
