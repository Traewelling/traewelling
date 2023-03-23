<?php

namespace App\Providers;

use App\Events\StatusDeleteEvent;
use App\Events\StatusUpdateEvent;
use App\Events\UserCheckedIn;
use App\Jobs\PostStatusOnMastodon;
use App\Listeners\NotificationSentWebhookListener;
use App\Listeners\StatusCreateWebhookListener;
use App\Listeners\StatusDeleteWebhookListener;
use App\Listeners\StatusUpdateWebhookListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class        => [
            //SendEmailVerificationNotification::class,
        ],
        UserCheckedIn::class     => [
            StatusCreateWebhookListener::class
        ],
        StatusUpdateEvent::class => [
            StatusUpdateWebhookListener::class
        ],
        StatusDeleteEvent::class => [
            StatusDeleteWebhookListener::class
        ],
        NotificationSent::class => [
            NotificationSentWebhookListener::class
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
        Event::listen(fn (UserCheckedIn $event)
        => PostStatusOnMastodon::dispatchIf($event->shouldPostOnMastodon, $event->status, $event->shouldChain));
        Event::listen(fn (WebhookCallFailedEvent $event) => Log::error("Webhook call failed", ['event' => $event]));
    }
}
