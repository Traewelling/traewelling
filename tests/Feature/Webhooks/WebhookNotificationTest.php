<?php

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Spatie\WebhookServer\CallWebhookJob;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;

class WebhookNotificationTest extends TestCase {
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

        Bus::assertDispatched(function (CallWebhookJob $job) {
            assertEquals(WebhookEvent::NOTIFICATION->name(), $job->payload['event']);
            return true;
        });
    }
}
