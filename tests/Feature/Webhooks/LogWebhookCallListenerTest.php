<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Listeners\LogWebhookCallListener;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCallLog;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Spatie\WebhookServer\Events\WebhookCallSucceededEvent;
use Tests\FeatureTestCase;

class LogWebhookCallListenerTest extends FeatureTestCase
{
    use RefreshDatabase;

    private function makeHeaders(Webhook $webhook, User $user): array
    {
        return [
            'X-Trwl-User-Id' => $user->id,
            'X-Trwl-Webhook-Id' => $webhook->id,
            'X-Trwl-OAuth-Client-Id' => $webhook->oauth_client_id,
        ];
    }

    public function test_logs_successful_webhook_call(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $event = new WebhookCallSucceededEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: ['event' => WebhookEvent::CHECKIN_CREATE->value],
            headers: $this->makeHeaders($webhook, $alice),
            meta: [],
            tags: [],
            attempt: 1,
            response: new Response(200),
            errorType: null,
            errorMessage: null,
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        (new LogWebhookCallListener())->handle($event);

        $log = WebhookCallLog::first();
        $this->assertNotNull($log);
        $this->assertEquals($webhook->id, $log->webhook_id);
        $this->assertEquals($alice->id, $log->user_id);
        $this->assertEquals($webhook->oauth_client_id, $log->oauth_client_id);
        $this->assertEquals(WebhookEvent::CHECKIN_CREATE->value, $log->event);
        $this->assertEquals($webhook->url, $log->url);
        $this->assertEquals(1, $log->attempt);
        $this->assertEquals(200, $log->response_code);
    }

    public function test_logs_failed_webhook_call_with_response_code(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $event = new WebhookCallFailedEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: ['event' => WebhookEvent::CHECKIN_CREATE->value],
            headers: $this->makeHeaders($webhook, $alice),
            meta: [],
            tags: [],
            attempt: 2,
            response: new Response(404),
            errorType: 'GuzzleHttp\Exception\ClientException',
            errorMessage: '404 Not Found',
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        (new LogWebhookCallListener())->handle($event);

        $log = WebhookCallLog::first();
        $this->assertNotNull($log);
        $this->assertEquals(2, $log->attempt);
        $this->assertEquals(404, $log->response_code);
    }

    public function test_logs_connection_error_as_null_response_code(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $event = new WebhookCallFailedEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: ['event' => WebhookEvent::CHECKIN_CREATE->value],
            headers: $this->makeHeaders($webhook, $alice),
            meta: [],
            tags: [],
            attempt: 1,
            response: null,
            errorType: 'GuzzleHttp\Exception\ConnectException',
            errorMessage: 'Unable to connect',
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        (new LogWebhookCallListener())->handle($event);

        $log = WebhookCallLog::first();
        $this->assertNotNull($log);
        $this->assertNull($log->response_code);
    }

    public function test_ignores_event_without_required_headers(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

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

        (new LogWebhookCallListener())->handle($event);

        $this->assertEquals(0, WebhookCallLog::count());
    }

    public function test_webhook_id_is_null_after_webhook_deletion(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhookId = $webhook->id;
        $oauthClientId = $webhook->oauth_client_id;

        $event = new WebhookCallSucceededEvent(
            httpVerb: 'POST',
            webhookUrl: $webhook->url,
            payload: ['event' => WebhookEvent::CHECKIN_CREATE->value],
            headers: $this->makeHeaders($webhook, $alice),
            meta: [],
            tags: [],
            attempt: 1,
            response: new Response(200),
            errorType: null,
            errorMessage: null,
            uuid: (string) Str::uuid(),
            transferStats: null
        );

        (new LogWebhookCallListener())->handle($event);
        $webhook->delete();

        $log = WebhookCallLog::first();
        $this->assertNotNull($log);
        $this->assertNull($log->webhook_id);
        $this->assertEquals($alice->id, $log->user_id);
        $this->assertEquals($oauthClientId, $log->oauth_client_id);
    }
}
