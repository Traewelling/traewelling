<?php

namespace App\Providers;

use App\Events\UserCheckedIn;
use App\Jobs\PostStatusOnMastodon;
use App\Jobs\PostStatusOnTwitter;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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

        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void {
        parent::boot();

        // Dispatch Jobs from Events
        Event::listen(fn(UserCheckedIn $event)
            => PostStatusOnMastodon::dispatchIf($event->shouldPostOnMastodon, $event->status, $event->shouldChain));
    }
}
