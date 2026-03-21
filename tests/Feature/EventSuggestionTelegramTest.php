<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Event\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;

class EventSuggestionTelegramTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    public function test_suggestion_sends_telegram_notification_and_stores_message_id(): void
    {
        config([
            'services.telegram.admin.active' => true,
            'services.telegram.admin.chat_id' => '123456',
            'services.telegram.admin.token' => 'test-token',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response(
                ['ok' => true, 'result' => ['message_id' => 42]],
                200,
            ),
        ]);

        $suggestion = new EventService()->suggestEvent(
            user: User::factory()->create(),
            name: $this->faker->name,
            begin: now(),
            end: now()->addDay(),
        );

        $this->assertSame(42, $suggestion->fresh()->admin_notification_id);
        Http::assertSentCount(1);
    }

    public function test_suggestion_is_persisted_even_when_telegram_api_fails(): void
    {
        config([
            'services.telegram.admin.active' => true,
            'services.telegram.admin.chat_id' => '123456',
            'services.telegram.admin.token' => 'test-token',
        ]);
        Http::fake([
            'https://api.telegram.org/*' => Http::response('Bad Request', 400),
        ]);

        $suggestion = new EventService()->suggestEvent(
            user: User::factory()->create(),
            name: $this->faker->name,
            begin: now(),
            end: now()->addDay(),
        );

        $this->assertDatabaseHas('event_suggestions', ['id' => $suggestion->id]);
        $this->assertNull($suggestion->fresh()->admin_notification_id);
    }
}
