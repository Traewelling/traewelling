<?php

declare(strict_types=1);

namespace Tests\Feature\Dev;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class UpdateAppTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_update_app_updates_name_and_redirect(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->actingAs($user)
            ->post(route('dev.apps.update', $client->id), [
                'name' => 'Updated Name',
                'redirect' => 'https://example.com/callback',
            ])
            ->assertRedirect(route('dev.apps'));

        $client->refresh();
        $this->assertEquals('Updated Name', $client->name);
        $this->assertEquals('https://example.com/callback', $client->redirect);
    }

    public function test_update_app_requires_authentication(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->post(route('dev.apps.update', $client->id), [
            'name' => 'Updated Name',
            'redirect' => 'https://example.com/callback',
        ])->assertRedirect(route('login'));
    }

    public function test_update_app_rejects_other_users_app(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $client = $this->createOAuthClient($owner, true);

        $this->actingAs($other)
            ->post(route('dev.apps.update', $client->id), [
                'name' => 'Hijacked',
                'redirect' => 'https://example.com/callback',
            ])
            ->assertNotFound();
    }

    public function test_update_app_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->actingAs($user)
            ->post(route('dev.apps.update', $client->id), [])
            ->assertSessionHasErrors(['name', 'redirect']);
    }

    public function test_update_app_validates_secure_urls(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->actingAs($user)
            ->post(route('dev.apps.update', $client->id), [
                'name' => 'Test App',
                'redirect' => 'https://example.com/callback',
                'authorized_webhook_url' => 'http://insecure.example.com/hook',
            ])
            ->assertSessionHasErrors(['authorized_webhook_url']);
    }
}
