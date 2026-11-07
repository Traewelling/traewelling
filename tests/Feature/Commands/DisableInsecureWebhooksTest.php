<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Enum\WebhookEvent;
use App\Http\Controllers\Backend\WebhookController;
use App\Models\OAuthClient;
use App\Models\User;
use App\Models\Webhook;
use App\Notifications\WebhookDisabled;
use App\Repositories\OAuthClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\FeatureTestCase;

class DisableInsecureWebhooksTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_disables_webhooks_pointing_to_non_public_targets(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);

        $insecure = $this->createWebhookWithUrl($user, $client, 'https://10.0.0.5/admin');
        $secure = $this->createWebhookWithUrl($user, $client, 'https://example.com/webhook');

        $this->artisan('app:disable-insecure-webhooks')->assertSuccessful();

        $this->assertNotNull($insecure->fresh()->disabled_at);
        $this->assertNull($secure->fresh()->disabled_at);

        Notification::assertSentTo($user, WebhookDisabled::class);
        Notification::assertSentToTimes($user, WebhookDisabled::class, 1);
    }

    public function test_removes_webhook_configuration_from_clients_with_non_public_targets(): void
    {
        $user = User::factory()->create();

        $insecure = (new OAuthClientRepository())->create(
            $user->id,
            'Insecure App',
            'https://example.com/callback',
            null,
            false,
            false,
            true,
            null,
            true,
            'https://169.254.169.254/webhook'
        );
        $secure = $this->createWebhookClient($user);

        $this->artisan('app:disable-insecure-webhooks')->assertSuccessful();

        $this->assertNull($insecure->fresh()->authorized_webhook_url);
        $this->assertFalse((bool) $insecure->fresh()->webhooks_enabled);

        $this->assertSame('https://example.com/webhook', $secure->fresh()->authorized_webhook_url);
        $this->assertTrue((bool) $secure->fresh()->webhooks_enabled);
    }

    private function createWebhookWithUrl(User $user, OAuthClient $client, string $url): Webhook
    {
        return WebhookController::createWebhook(
            WebhookController::createWebhookRequest(
                $user,
                $client,
                'disable-insecure-webhooks-' . $url,
                $url,
                [WebhookEvent::CHECKIN_CREATE->value]
            )
        );
    }
}
