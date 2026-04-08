<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Listeners\DisableFailingWebhookListener;
use App\Listeners\ResetWebhookFailureCountListener;
use App\Models\User;
use App\Models\Webhook;
use App\Notifications\WebhookDisabled;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Spatie\WebhookServer\Events\FinalWebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
use Tests\FeatureTestCase;

class DisableFailingWebhookListenerTest extends FeatureTestCase
{
    use RefreshDatabase;

    private function makeFinalFailedEvent(Webhook $webhook, User $user): FinalWebhookCallFailedEvent
    {
        return new FinalWebhookCallFailedEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: ['not' => 'relevant'],
            headers: [
                'X-Trwl-User-Id' => $user->id,
                'X-Trwl-Webhook-Id' => $webhook->id,
            ],
            meta: ['not' => 'relevant'],
            tags: ['not' => 'relevant'],
            attempt: 3,
            response: null,
            errorType: 'GuzzleHttp\Exception\ConnectException',
            errorMessage: 'Unable to connect',
            uuid: (string) Str::uuid(),
            transferStats: null
        );
    }

    public function test_increments_failure_count_below_threshold(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $listener = new DisableFailingWebhookListener();
        $listener->handle($this->makeFinalFailedEvent($webhook, $alice));

        $webhook->refresh();
        $this->assertEquals(1, $webhook->consecutive_failures);
        $this->assertNull($webhook->disabled_at);
    }

    public function test_disables_webhook_after_threshold(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhook->update(['consecutive_failures' => 4]);

        $listener = new DisableFailingWebhookListener();
        $listener->handle($this->makeFinalFailedEvent($webhook, $alice));

        $webhook->refresh();
        $this->assertEquals(5, $webhook->consecutive_failures);
        $this->assertNotNull($webhook->disabled_at);
    }

    public function test_sends_notification_when_disabled(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhook->update(['consecutive_failures' => 4]);

        $listener = new DisableFailingWebhookListener();
        $listener->handle($this->makeFinalFailedEvent($webhook, $alice));

        $this->assertEquals(
            1,
            DatabaseNotification::where('notifiable_id', $alice->id)
                ->where('type', WebhookDisabled::class)
                ->count()
        );
    }

    public function test_does_not_disable_already_disabled_webhook(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $disabledAt = now()->subDay();
        $webhook->update(['consecutive_failures' => 10, 'disabled_at' => $disabledAt]);

        $listener = new DisableFailingWebhookListener();
        $listener->handle($this->makeFinalFailedEvent($webhook, $alice));

        $webhook->refresh();
        // Failure count must not be incremented for an already-disabled webhook
        $this->assertEquals(10, $webhook->consecutive_failures);
        $this->assertEquals($disabledAt->toDateTimeString(), $webhook->disabled_at->toDateTimeString());

        // No additional notification
        $this->assertEquals(0, DatabaseNotification::where('notifiable_id', $alice->id)->count());
    }

    public function test_ignores_event_without_webhook_id_header(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $event = new FinalWebhookCallFailedEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: [],
            headers: [],
            meta: [],
            tags: [],
            attempt: 3,
            response: null,
            errorType: 'GuzzleHttp\Exception\ConnectException',
            errorMessage: 'Unable to connect',
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        $listener = new DisableFailingWebhookListener();
        $listener->handle($event);

        $webhook->refresh();
        $this->assertEquals(0, $webhook->consecutive_failures);
        $this->assertNull($webhook->disabled_at);
    }

    // --- ResetWebhookFailureCountListener ---

    public function test_reset_listener_clears_failure_count_on_success(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhook->update(['consecutive_failures' => 3]);

        $event = new WebhookCallSucceededEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: [],
            headers: ['X-Trwl-Webhook-Id' => $webhook->id],
            meta: [],
            tags: [],
            attempt: 1,
            response: new Response(200),
            errorType: null,
            errorMessage: null,
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        (new ResetWebhookFailureCountListener())->handle($event);

        $webhook->refresh();
        $this->assertEquals(0, $webhook->consecutive_failures);
    }

    public function test_reset_listener_ignores_event_without_webhook_id_header(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhook->update(['consecutive_failures' => 2]);

        $event = new WebhookCallSucceededEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: [],
            headers: [],
            meta: [],
            tags: [],
            attempt: 1,
            response: new Response(200),
            errorType: null,
            errorMessage: null,
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        (new ResetWebhookFailureCountListener())->handle($event);

        $webhook->refresh();
        $this->assertEquals(2, $webhook->consecutive_failures);
    }

    // --- WebhookDisabled notification ---

    public function test_webhook_disabled_notification_lead(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhook->update(['consecutive_failures' => 4]);

        (new DisableFailingWebhookListener())->handle($this->makeFinalFailedEvent($webhook, $alice));

        $notification = DatabaseNotification::where('notifiable_id', $alice->id)
            ->where('type', WebhookDisabled::class)
            ->firstOrFail();

        $lead = WebhookDisabled::getLead($notification->data);
        $this->assertNotEmpty($lead);

        $notice = WebhookDisabled::getNotice($notification->data);
        $this->assertStringContainsString($client->name, $notice);

        $link = WebhookDisabled::getLink($notification->data);
        $this->assertStringContainsString('settings/security', $link);
    }
}
