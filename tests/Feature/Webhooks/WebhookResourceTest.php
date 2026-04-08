<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class WebhookResourceTest extends ApiTestCase
{
    use RefreshDatabase;

    private function webhookStructure(): array
    {
        return [
            'id',
            'clientId',
            'client',
            'userId',
            'user',
            'url',
            'createdAt',
            'events',
            'disabledAt',
        ];
    }

    public function test_index_returns_webhook_resource_structure(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson('/api/v1/webhooks');

        $response->assertOk();
        $response->assertJsonStructure(['data' => [['id', 'clientId', 'client', 'userId', 'user', 'url', 'createdAt', 'events', 'disabledAt']]]);
        $response->assertJsonPath('data.0.disabledAt', null);
    }

    public function test_show_returns_webhook_resource_structure(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson("/api/v1/webhooks/{$webhook->id}");

        $response->assertOk();
        $response->assertJsonStructure(['data' => $this->webhookStructure()]);
        $response->assertJsonPath('data.disabledAt', null);
    }

    public function test_disabled_at_is_present_when_webhook_is_disabled(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);
        $webhook->update(['disabled_at' => now()]);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson("/api/v1/webhooks/{$webhook->id}");

        $response->assertOk();
        $response->assertJsonPath('data.disabledAt', $webhook->fresh()->disabled_at->toIso8601String());
    }

    public function test_unauthenticated_cannot_access_webhooks(): void
    {
        $response = $this->getJson('/api/v1/webhooks');
        $response->assertUnauthorized();
    }

    public function test_user_cannot_see_other_users_webhook(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $client = $this->createWebhookClient($alice);
        $webhook = $this->createWebhook($alice, $client, [WebhookEvent::CHECKIN_CREATE]);

        $this->actAsApiUserWithAllScopes($bob);
        $response = $this->getJson("/api/v1/webhooks/{$webhook->id}");

        $response->assertNotFound();
    }
}
