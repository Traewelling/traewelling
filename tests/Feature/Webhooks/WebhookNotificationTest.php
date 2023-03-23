<?php

namespace Tests\Feature\Webhooks;

use App\Enum\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Spatie\WebhookServer\CallWebhookJob;
use Tests\TestCase;
use function PHPUnit\Framework\assertEquals;

class WebhookNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void {
        if (config("trwl.webhooks_active") !== "true") {
            $this->markTestSkipped();
        }
    }

    public function testWebhookSendingOnNotification(): void {
        Bus::fake();

        $alice = $this->createGDPRAckedUser();
        $bob   = $this->createGDPRAckedUser();

        $client = $this->createWebhookClient($alice);
        $this->createWebhook($bob, $client, [WebhookEvent::NOTIFICATION]);

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        Bus::assertDispatched(static function(CallWebhookJob $job) {
            assertEquals(WebhookEvent::NOTIFICATION->name(), $job->payload['event']);
            return true;
        });
    }
}
