<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class ApplicationCrudTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_can_list_own_applications(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->getJson('/api/v1/applications');

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $client->id);
        $response->assertJsonPath('data.0.name', $client->name);
        $response->assertJsonStructure(['data' => [['id', 'name', 'redirect', 'confidential', 'webhooksEnabled', 'activeTokensCount']]]);
    }

    public function test_user_does_not_see_other_users_applications(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $this->createOAuthClient($bob, true);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->getJson('/api/v1/applications');

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_list_applications_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/applications');
        $response->assertUnauthorized();
    }

    public function test_user_can_create_application(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->postJson('/api/v1/applications', [
            'name' => 'My Test App',
            'redirect' => 'https://example.com/callback',
            'confidential' => true,
            'webhooksEnabled' => false,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.name', 'My Test App');
        $response->assertJsonPath('data.redirect', 'https://example.com/callback');
        $response->assertJsonPath('data.confidential', true);
    }

    public function test_create_application_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->postJson('/api/v1/applications', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'redirect']);
    }

    public static function redirectUrlProvider(): array
    {
        return [
            'https allowed' => ['https://example.com/callback', true],
            'plain http rejected' => ['http://example.com/callback', false],
            'localhost' => ['http://localhost:8710/callback', true],
            'localhost subdomain' => ['http://app.localhost/callback', true],
            'ipv4 loopback' => ['http://127.0.0.1:8710/callback', true],
            'ipv4 loopback subnet' => ['http://127.1.2.3/callback', true],
            'ipv6 loopback' => ['http://[::1]:8710/callback', true],
        ];
    }

    #[DataProvider('redirectUrlProvider')]
    public function test_create_application_validates_redirect_url_scheme(string $redirect, bool $allowed): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->postJson('/api/v1/applications', [
            'name' => 'App',
            'redirect' => $redirect,
        ]);

        if ($allowed) {
            $response->assertCreated();
            $response->assertJsonPath('data.redirect', $redirect);
        } else {
            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['redirect']);
        }
    }

    public static function webhookUrlProvider(): array
    {
        return [
            'https to public host allowed' => ['https://example.com/webhook', true],
            'https to public ip allowed' => ['https://8.8.8.8/webhook', true],
            'plain http rejected' => ['http://example.com/webhook', false],
            'http to ipv4 loopback rejected' => ['http://127.0.0.1:18080/internal-admin', false],
            'http to localhost rejected' => ['http://localhost:6379/', false],
            'https to ipv4 loopback rejected' => ['https://127.0.0.1:18080/internal-admin', false],
            'https to private ipv4 rejected' => ['https://10.0.0.5/admin', false],
            'https to ipv4 link-local rejected' => ['https://169.254.169.254/latest', false],
            'https to ipv6 loopback rejected' => ['https://[::1]/internal-admin', false],
            'https to unresolvable host rejected' => ['https://nonexistent-host.invalid/webhook', false],
        ];
    }

    #[DataProvider('webhookUrlProvider')]
    public function test_create_application_validates_webhook_url(string $webhookUrl, bool $allowed): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->postJson('/api/v1/applications', [
            'name' => 'App',
            'redirect' => 'https://example.com/callback',
            'webhooksEnabled' => true,
            'authorizedWebhookUrl' => $webhookUrl,
        ]);

        if ($allowed) {
            $response->assertCreated();
            $response->assertJsonPath('data.authorizedWebhookUrl', $webhookUrl);
        } else {
            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['authorizedWebhookUrl']);
        }
    }

    public function test_create_application_returns_plain_secret_for_confidential(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->postJson('/api/v1/applications', [
            'name' => 'Secret App',
            'redirect' => 'https://example.com/callback',
            'confidential' => true,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.confidential', true);
        $this->assertNotNull($response->json('data.plainSecret'));
    }

    public function test_create_application_requires_auth(): void
    {
        $response = $this->postJson('/api/v1/applications', [
            'name' => 'App',
            'redirect' => 'https://example.com/callback',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_update_own_application(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->putJson("/api/v1/applications/{$client->id}", [
            'name' => 'Updated Name',
            'redirect' => 'https://updated.example.com/callback',
            'confidential' => true,
            'webhooksEnabled' => false,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated Name');
        $response->assertJsonPath('data.redirect', 'https://updated.example.com/callback');
    }

    public function test_user_cannot_update_other_users_application(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $client = $this->createOAuthClient($bob, true);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->putJson("/api/v1/applications/{$client->id}", [
            'name' => 'Hacked',
            'redirect' => 'https://example.com/callback',
        ]);

        $response->assertNotFound();
    }

    public function test_update_application_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->putJson('/api/v1/applications/999999', [
            'name' => 'App',
            'redirect' => 'https://example.com/callback',
        ]);

        $response->assertNotFound();
    }

    public function test_update_application_requires_auth(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $response = $this->putJson("/api/v1/applications/{$client->id}", [
            'name' => 'App',
            'redirect' => 'https://example.com/callback',
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_delete_own_application(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->deleteJson("/api/v1/applications/{$client->id}");

        $response->assertNoContent();

        $list = $this->getJson('/api/v1/applications');
        $list->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_delete_other_users_application(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $client = $this->createOAuthClient($bob, true);

        $this->actAsApiUserWithAllScopes($alice);
        $response = $this->deleteJson("/api/v1/applications/{$client->id}");

        $response->assertNotFound();
    }

    public function test_delete_application_returns_404_for_nonexistent(): void
    {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->deleteJson('/api/v1/applications/999999');

        $response->assertNotFound();
    }

    public function test_delete_application_requires_auth(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $response = $this->deleteJson("/api/v1/applications/{$client->id}");

        $response->assertUnauthorized();
    }
}
