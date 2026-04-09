<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\WebhookEvent;
use App\Models\User;
use App\Models\WebhookCallLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ApplicationWebhookStatsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_own_app_stats(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);

        WebhookCallLog::factory()->create([
            'oauth_client_id' => $client->id,
            'user_id' => $alice->id,
            'event' => WebhookEvent::CHECKIN_CREATE->value,
            'url' => 'https://example.com/webhook',
            'attempt' => 1,
            'response_code' => 200,
            'created_at' => now()->subHours(2),
        ]);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson("/api/v1/applications/{$client->id}/webhook-stats");

        $response->assertOk();
        $response->assertJsonPath('data.clientId', $client->id);
        $response->assertJsonPath('data.clientName', $client->name);
        $response->assertJsonPath('data.total', 1);
        $response->assertJsonStructure(['data' => ['clientId', 'clientName', 'total', 'byDay', 'byEvent', 'byResponseCode']]);
    }

    public function test_admin_can_access_any_app_stats(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);

        $admin = $this->newUserWithRole('admin');
        $this->actAsApiUserWithAllScopes($admin);

        $response = $this->getJson("/api/v1/applications/{$client->id}/webhook-stats");

        $response->assertOk();
        $response->assertJsonPath('data.clientId', $client->id);
    }

    public function test_user_cannot_access_other_users_app_stats(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $client = $this->createWebhookClient($bob);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson("/api/v1/applications/{$client->id}/webhook-stats");

        $response->assertNotFound();
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);

        $response = $this->getJson("/api/v1/applications/{$client->id}/webhook-stats");

        $response->assertUnauthorized();
    }

    public function test_stats_exclude_logs_older_than_7_days(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);

        WebhookCallLog::factory()->create([
            'oauth_client_id' => $client->id,
            'user_id' => $alice->id,
            'event' => WebhookEvent::CHECKIN_CREATE->value,
            'url' => 'https://example.com/webhook',
            'attempt' => 1,
            'response_code' => 200,
            'created_at' => now()->subDays(8),
        ]);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson("/api/v1/applications/{$client->id}/webhook-stats");

        $response->assertOk();
        $response->assertJsonPath('data.total', 0);
    }

    public function test_timeout_shows_as_null_response_code(): void
    {
        $alice = User::factory()->create();
        $client = $this->createWebhookClient($alice);

        WebhookCallLog::factory()->create([
            'oauth_client_id' => $client->id,
            'user_id' => $alice->id,
            'event' => WebhookEvent::CHECKIN_CREATE->value,
            'url' => 'https://example.com/webhook',
            'attempt' => 1,
            'response_code' => null,
            'created_at' => now()->subHour(),
        ]);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson("/api/v1/applications/{$client->id}/webhook-stats");

        $response->assertOk();
        $response->assertJsonPath('data.byResponseCode.0.responseCode', null);
        $response->assertJsonPath('data.byResponseCode.0.total', 1);
    }

    private function newUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
