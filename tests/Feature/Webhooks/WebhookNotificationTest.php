<?php

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Jobs\MonitoredCallWebhookJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Passport;

use function PHPUnit\Framework\assertEquals;

use Tests\FeatureTestCase;

class WebhookNotificationTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_webhook_sending_on_notification()
    {
        Bus::fake();

        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $client = $this->createWebhookClient($alice);
        $this->createWebhook($bob, $client, [WebhookEvent::NOTIFICATION]);

        // When: Alice follows Bob
        Passport::actingAs($alice, ['*']);
        $this->postJson('/api/v1/user/' . $bob->id . '/follow')->assertStatus(201);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) {
            assertEquals(WebhookEvent::NOTIFICATION->value, $job->payload['event']);

            return true;
        });
    }
}
