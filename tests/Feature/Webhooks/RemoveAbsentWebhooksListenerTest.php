<?php

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Listeners\RemoveAbsentWebhooksListener;
use App\Models\User;
use App\Models\Webhook;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\WebhookServer\Events\WebhookCallFailedEvent;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class RemoveAbsentWebhooksListenerTest extends TestCase
{
    use RefreshDatabase;

    public function testItRemovesAbsentWebhook() {
        // GIVEN
        $alice   = User::factory()->create();
        $client  = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::NOTIFICATION]);
        assertEquals(1, Webhook::where("id", "=", $webhook->id)->count());

        // WHEN
        $underTest = new RemoveAbsentWebhooksListener();
        $underTest->handle(
            new WebhookCallFailedEvent(
                httpVerb:      "POST",
                webhookUrl:    $webhook->url,
                payload:       ["not" => "relevant"],
                headers:       [
                                   "X-Trwl-User-Id"    => $alice->id,
                                   "X-Trwl-Webhook-Id" => $webhook->id,
                               ],
                meta:          ["not" => "relevant"],
                tags:          ["not" => "relevant"],
                attempt:       1,
                response:      new Response(410 /* GONE */),
                errorType:     "GuzzleHttp\Exception\ClientException",
                errorMessage:  "410 Gone",
                uuid:          Str::uuid(),
                transferStats: null
            ));

        // THEN
        assertEquals(0, Webhook::where("id", "=", $webhook->id)->count());
    }

    public function testItDoesntInteractWhenOtherReturnCode() {
        // GIVEN
        $alice   = User::factory()->create();
        $client  = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::NOTIFICATION]);
        assertEquals(1, Webhook::where("id", "=", $webhook->id)->count());

        // WHEN
        $underTest = new RemoveAbsentWebhooksListener();

        $underTest->handle(
            new WebhookCallFailedEvent(
                httpVerb:      "POST",
                webhookUrl:    $webhook->url,
                payload:       ["not" => "relevant"],
                headers:       ["not" => "relevant"],
                meta:          ["not" => "relevant"],
                tags:          ["not" => "relevant"],
                attempt:       1,
                response:      new Response(503 /* SERVICE UNAVAILABLE */),
                errorType:     "GuzzleHttp\Exception\ClientException",
                errorMessage:  "503 Service Unavailable",
                uuid:          Str::uuid(),
                transferStats: null
            ));

        // THEN
        assertEquals(1, Webhook::where("id", "=", $webhook->id)->count());
    }

    public function testItDoesntInteractWhenConnectException() {
        // GIVEN
        $alice   = User::factory()->create();
        $client  = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::NOTIFICATION]);
        assertEquals(1, Webhook::where("id", "=", $webhook->id)->count());

        // WHEN
        $underTest = new RemoveAbsentWebhooksListener();
        $underTest->handle(new WebhookCallFailedEvent(
                               httpVerb:      "POST",
                               webhookUrl:    $webhook->url,
                               payload:       ["not" => "relevant"],
                               headers:       ["not" => "relevant"],
                               meta:          ["not" => "relevant"],
                               tags:          ["not" => "relevant"],
                               attempt:       1,
                               response:      null,
                               errorType:     "GuzzleHttp\Exception\ConnectException",
                               errorMessage:  "Unable to connect to Server",
                               uuid:          Str::uuid(),
                               transferStats: null
                           ));

        // THEN
        assertEquals(1, Webhook::where("id", "=", $webhook->id)->count());
    }

    public function testItThrowsWhenWebhookToDeleteCannotBeFound() {
        // GIVEN
        $alice = User::factory()->create();

        $unknown_webhook_id = 42;
        assertEquals(0, Webhook::where("id", "=", $unknown_webhook_id)->count());

        // WHEN
        $underTest = new RemoveAbsentWebhooksListener();

        $this->assertThrows(fn() => $underTest->handle(
            new WebhookCallFailedEvent(
                httpVerb:      "POST",
                webhookUrl:    "https://example.com/trwl-webhook",
                payload:       ["not" => "relevant"],
                headers:       [
                                   "X-Trwl-User-Id"    => $alice->id,
                                   "X-Trwl-Webhook-Id" => $unknown_webhook_id,
                               ],
                meta:          ["not" => "relevant"],
                tags:          ["not" => "relevant"],
                attempt:       1,
                response:      new Response(410 /* GONE */),
                errorType:     "GuzzleHttp\Exception\ClientException",
                errorMessage:  "410 Gone",
                uuid:          Str::uuid(),
                transferStats: null
            )),
            ModelNotFoundException::class);
    }
}
