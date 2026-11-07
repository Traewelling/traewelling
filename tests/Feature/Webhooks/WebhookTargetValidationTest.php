<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Http\Controllers\Backend\WebhookController;
use App\Models\Checkin;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookCallLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;

class WebhookTargetValidationTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_webhooks_to_private_targets_are_not_delivered(): void
    {
        Http::fake();

        [$user, $webhook] = $this->userWithWebhook('https://10.0.0.5/internal-admin');

        $this->fireCheckinWebhook($user);

        Http::assertNothingSent();

        $log = WebhookCallLog::where('webhook_id', $webhook->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('https://10.0.0.5/internal-admin', $log->url);
        $this->assertNull($log->response_code);

        $this->assertSame(1, $webhook->fresh()->consecutive_failures);
    }

    public function test_webhooks_to_loopback_targets_are_not_delivered(): void
    {
        Http::fake();

        [$user, $webhook] = $this->userWithWebhook('http://127.0.0.1:18080/internal-admin');

        $this->fireCheckinWebhook($user);

        Http::assertNothingSent();
        $this->assertSame(1, $webhook->fresh()->consecutive_failures);
    }

    public function test_webhooks_to_public_targets_are_delivered(): void
    {
        Http::fake(['example.com/*' => Http::response(status: 200)]);

        [$user, $webhook] = $this->userWithWebhook('https://example.com/webhook');

        $this->fireCheckinWebhook($user);

        Http::assertSent(
            fn ($request) => $request->url() === 'https://example.com/webhook'
                && $request->hasHeader('X-Trwl-Webhook-Id', (string) $webhook->id)
        );

        $log = WebhookCallLog::where('webhook_id', $webhook->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(200, $log->response_code);
        $this->assertSame(0, $webhook->fresh()->consecutive_failures);
    }

    /**
     * @return array{User, Webhook}
     */
    private function userWithWebhook(string $url): array
    {
        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);

        $webhook = WebhookController::createWebhook(
            WebhookController::createWebhookRequest(
                $user,
                $client,
                'webhook-target-validation',
                $url,
                [WebhookEvent::CHECKIN_CREATE->value]
            )
        );

        return [$user, $webhook];
    }

    private function fireCheckinWebhook(User $user): void
    {
        $checkin = Checkin::factory()->create(['user_id' => $user->id]);

        WebhookController::sendStatusWebhook($checkin->status, WebhookEvent::CHECKIN_CREATE);
    }
}
