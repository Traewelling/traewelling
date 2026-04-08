<?php

namespace App\Providers;

use App\Events\StatusUpdateEvent;
use App\Events\UserCheckedIn;
use App\Jobs\PostStatusOnMastodon;
use App\Listeners\CacheMissedListener;
use App\Listeners\DisableFailingWebhookListener;
use App\Listeners\LogWebhookCallListener;
use App\Listeners\NotificationSentWebhookListener;
use App\Listeners\RemoveAbsentWebhooksListener;
use App\Listeners\ResetWebhookFailureCountListener;
use App\Listeners\StatusCreateCheckPolylineListener;
use App\Listeners\StatusCreateWebhookListener;
use App\Listeners\StatusUpdateWebhookListener;
use App\Models\Checkin;
use App\Models\EventSuggestion;
use App\Models\Follow;
use App\Models\FollowRequest;
use App\Models\Like;
use App\Models\Report;
use App\Models\RouteSegment;
use App\Models\Status;
use App\Models\Trip;
use App\Models\User;
use App\Observers\CheckinObserver;
use App\Observers\EventSuggestionObserver;
use App\Observers\FollowObserver;
use App\Observers\FollowRequestObserver;
use App\Observers\LikeObserver;
use App\Observers\ReportObserver;
use App\Observers\RouteSegmentObserver;
use App\Observers\StatusObserver;
use App\Observers\TripObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [],
        UserCheckedIn::class => [
            StatusCreateWebhookListener::class,
            StatusCreateCheckPolylineListener::class,
        ],
        StatusUpdateEvent::class => [
            StatusUpdateWebhookListener::class,
        ],
        NotificationSent::class => [
            NotificationSentWebhookListener::class,
        ],
        WebhookCallFailedEvent::class => [
            RemoveAbsentWebhooksListener::class,
            LogWebhookCallListener::class,
        ],
        FinalWebhookCallFailedEvent::class => [
            DisableFailingWebhookListener::class,
        ],
        WebhookCallSucceededEvent::class => [
            ResetWebhookFailureCountListener::class,
            LogWebhookCallListener::class,
        ],
        CacheMissed::class => [
            CacheMissedListener::class,
        ],
    ];

    protected $observers = [
        Checkin::class => [CheckinObserver::class],
        EventSuggestion::class => [EventSuggestionObserver::class],
        Follow::class => [FollowObserver::class],
        FollowRequest::class => [FollowRequestObserver::class],
        Like::class => [LikeObserver::class],
        Report::class => [ReportObserver::class],
        RouteSegment::class => [RouteSegmentObserver::class],
        Status::class => [StatusObserver::class],
        Trip::class => [TripObserver::class],
        User::class => [UserObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Dispatch Jobs from Events
        Event::listen(fn (UserCheckedIn $event) => PostStatusOnMastodon::dispatchIf($event->shouldPostOnMastodon, $event->status, $event->shouldChain));
        Event::listen(function (WebhookCallFailedEvent $event) {
            $payload = $event->payload;
            if (is_string($payload)) {
                $decoded = json_decode($payload, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $payload = $decoded;
                }
            }

            Log::warning('Webhook call failed', [
                'webhook_id' => $event->headers['X-Trwl-Webhook-Id'] ?? null,
                'user_id' => $event->headers['X-Trwl-User-Id'] ?? null,
                'webhook_url' => $event->webhookUrl,
                'event_type' => is_array($payload) ? ($payload['event'] ?? null) : null,
                'attempt' => $event->attempt,
                'error_type' => $event->errorType,
                'error_message' => $event->errorMessage,
            ]);
        });
    }
}
