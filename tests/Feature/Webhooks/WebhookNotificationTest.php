<?php

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Jobs\MonitoredCallWebhookJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\FeatureTestCase;

use function PHPUnit\Framework\assertEquals;

class WebhookNotificationTest extends FeatureTestCase {
    use RefreshDatabase;

    public function testWebhookSendingOnNotification() {
        Bus::fake();

        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        $client = $this->createWebhookClient($alice);
        $this->createWebhook($bob, $client, [WebhookEvent::NOTIFICATION]);

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        Bus::assertDispatched(function (MonitoredCallWebhookJob $job) {
            assertEquals(WebhookEvent::NOTIFICATION->value, $job->payload['event']);
            return true;
        });
    }
}
