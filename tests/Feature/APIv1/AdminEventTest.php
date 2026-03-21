<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\EventRejectionReason;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Station;
use App\Models\User;
use App\Notifications\EventSuggestionProcessed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\ApiTestCase;

class AdminEventTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $moderator;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->moderator = User::factory()->create();
        $this->moderator->givePermissionTo([
            'view-backend',
            'view-events',
            'accept-events',
            'deny-events',
            'update-events',
        ]);

        $this->user = User::factory()->create();
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Event',
            'checkin_start' => now()->addDay()->toDateString(),
            'checkin_end' => now()->addDays(5)->toDateString(),
        ], $overrides);
    }

    public function test_guest_cannot_list_events(): void
    {
        $this->getJson('/api/v1/admin/events')->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_list_events(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson('/api/v1/admin/events')->assertForbidden();
    }

    public function test_index_returns_events_filtered_by_status(): void
    {
        $future = Event::factory()->create(['checkin_start' => now()->addDays(5)->toDateString(), 'checkin_end' => now()->addDays(10)->toDateString()]);
        $current = Event::factory()->create(['checkin_start' => now()->subDay()->toDateString(), 'checkin_end' => now()->addDay()->toDateString()]);
        $past = Event::factory()->create(['checkin_start' => now()->subDays(10)->toDateString(), 'checkin_end' => now()->subDay()->toDateString()]);

        $this->actAsApiUserWithAllScopes($this->moderator);

        $this->getJson('/api/v1/admin/events?status=future')
            ->assertOk()
            ->assertJsonFragment(['id' => $future->id])
            ->assertJsonMissing(['id' => $current->id]);

        $this->getJson('/api/v1/admin/events?status=current')
            ->assertOk()
            ->assertJsonFragment(['id' => $current->id])
            ->assertJsonMissing(['id' => $past->id]);

        $this->getJson('/api/v1/admin/events?status=past')
            ->assertOk()
            ->assertJsonFragment(['id' => $past->id])
            ->assertJsonMissing(['id' => $future->id]);
    }

    public function test_index_filters_by_search(): void
    {
        $match = Event::factory()->create(['name' => 'Berliner Bahnhofsfest', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);
        $noMatch = Event::factory()->create(['name' => 'Hamburger Hafengeburtstag', 'checkin_start' => now()->addDay()->toDateString(), 'checkin_end' => now()->addDays(3)->toDateString()]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->getJson('/api/v1/admin/events?search=Berliner')
            ->assertOk()
            ->assertJsonFragment(['id' => $match->id])
            ->assertJsonMissing(['id' => $noMatch->id]);
    }

    public function test_show_returns_event(): void
    {
        $event = Event::factory()->create();

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->getJson("/api/v1/admin/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonPath('data.name', $event->name);
    }

    public function test_show_returns_404_for_unknown_event(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->getJson('/api/v1/admin/events/99999')->assertNotFound();
    }

    public function test_create_event_stores_record(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->postJson('/api/v1/admin/events', $this->eventPayload(['name' => 'New API Event']))
            ->assertCreated()
            ->assertJsonPath('data.name', 'New API Event');

        $this->assertDatabaseHas('events', ['name' => 'New API Event']);
    }

    public function test_create_requires_permission(): void
    {
        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->postJson('/api/v1/admin/events', $this->eventPayload())
            ->assertForbidden();
    }

    public function test_create_validates_required_fields(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->postJson('/api/v1/admin/events', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'checkin_start', 'checkin_end']);
    }

    public function test_create_with_station_id_links_station(): void
    {
        $station = Station::factory()->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->postJson('/api/v1/admin/events', $this->eventPayload(['station_id' => $station->id]))
            ->assertCreated()
            ->assertJsonPath('data.station.id', $station->id);
    }

    public function test_update_event_changes_name(): void
    {
        $event = Event::factory()->create();

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->putJson("/api/v1/admin/events/{$event->id}", $this->eventPayload(['name' => 'Updated Name']))
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => 'Updated Name']);
    }

    public function test_update_clears_station_when_station_id_is_null(): void
    {
        $station = Station::factory()->create();
        $event = Event::factory()->create(['station_id' => $station->id]);

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->putJson("/api/v1/admin/events/{$event->id}", $this->eventPayload(['station_id' => null]))
            ->assertOk()
            ->assertJsonPath('data.station', null);

        $this->assertDatabaseHas('events', ['id' => $event->id, 'station_id' => null]);
    }

    public function test_update_keeps_station_when_station_id_omitted(): void
    {
        $station = Station::factory()->create();
        $event = Event::factory()->create(['station_id' => $station->id]);

        $this->actAsApiUserWithAllScopes($this->admin);
        $payload = $this->eventPayload();
        unset($payload['station_id']);

        $this->putJson("/api/v1/admin/events/{$event->id}", $payload)
            ->assertOk()
            ->assertJsonPath('data.station.id', $station->id);
    }

    public function test_update_validates_required_fields(): void
    {
        $event = Event::factory()->create();

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->putJson("/api/v1/admin/events/{$event->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'checkin_start', 'checkin_end']);
    }

    public function test_delete_removes_event(): void
    {
        $event = Event::factory()->create();

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson("/api/v1/admin/events/{$event->id}")->assertNoContent();

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_delete_requires_admin_permission(): void
    {
        $event = Event::factory()->create();

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->deleteJson("/api/v1/admin/events/{$event->id}")->assertForbidden();
    }

    public function test_delete_returns_404_for_unknown_event(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);
        $this->deleteJson('/api/v1/admin/events/99999')->assertNotFound();
    }

    public function test_list_suggestions_returns_only_open_future_suggestions(): void
    {
        $open = EventSuggestion::factory()->create(['processed' => false, 'end' => now()->addDays(5)->toDateString()]);
        $closed = EventSuggestion::factory()->create(['processed' => true, 'end' => now()->addDays(5)->toDateString()]);
        $past = EventSuggestion::factory()->create(['processed' => false, 'end' => now()->subDay()->toDateString()]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->getJson('/api/v1/admin/event-suggestions')
            ->assertOk()
            ->assertJsonFragment(['id' => $open->id])
            ->assertJsonMissing(['id' => $closed->id])
            ->assertJsonMissing(['id' => $past->id]);
    }

    public function test_show_suggestion_returns_suggestion_and_parallel_events(): void
    {
        $suggestion = EventSuggestion::factory()->create([
            'processed' => false,
            'begin' => now()->addDay()->toDateString(),
            'end' => now()->addDays(5)->toDateString(),
        ]);
        $parallel = Event::factory()->create([
            'checkin_start' => now()->addDays(2)->toDateString(),
            'checkin_end' => now()->addDays(4)->toDateString(),
        ]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->getJson("/api/v1/admin/event-suggestions/{$suggestion->id}")
            ->assertOk()
            ->assertJsonPath('data.suggestion.id', $suggestion->id)
            ->assertJsonFragment(['id' => $parallel->id]);
    }

    public function test_show_suggestion_hides_user_for_moderator(): void
    {
        $suggestion = EventSuggestion::factory()->create(['processed' => false]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->getJson("/api/v1/admin/event-suggestions/{$suggestion->id}")
            ->assertOk()
            ->assertJsonPath('data.suggestion.user', null);
    }

    public function test_show_suggestion_exposes_user_for_admin(): void
    {
        $suggestion = EventSuggestion::factory()->create(['processed' => false]);

        $this->actAsApiUserWithAllScopes($this->admin);
        $this->getJson("/api/v1/admin/event-suggestions/{$suggestion->id}")
            ->assertOk()
            ->assertJsonPath('data.suggestion.user.id', $suggestion->user_id);
    }

    public function test_accept_suggestion_creates_event_and_marks_processed(): void
    {
        Notification::fake();
        config(['services.telegram.admin.active' => false]);

        $suggestion = EventSuggestion::factory()->create(['processed' => false]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->postJson("/api/v1/admin/event-suggestions/{$suggestion->id}/accept", [
            'name' => 'Accepted Event',
            'checkin_start' => now()->addDay()->toDateString(),
            'checkin_end' => now()->addDays(5)->toDateString(),
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Accepted Event');

        $this->assertDatabaseHas('events', ['name' => 'Accepted Event']);
        $this->assertDatabaseHas('event_suggestions', ['id' => $suggestion->id, 'processed' => true]);
        Notification::assertSentTo($suggestion->user, EventSuggestionProcessed::class);
    }

    public function test_user_cannot_accept_own_suggestion(): void
    {
        $suggestion = EventSuggestion::factory()->create([
            'processed' => false,
            'user_id' => $this->moderator->id,
        ]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->postJson("/api/v1/admin/event-suggestions/{$suggestion->id}/accept", [
            'name' => 'Self-accepted',
            'checkin_start' => now()->addDay()->toDateString(),
            'checkin_end' => now()->addDays(5)->toDateString(),
        ])->assertForbidden();

        $this->assertDatabaseMissing('events', ['name' => 'Self-accepted']);
    }

    public function test_accept_validates_required_fields(): void
    {
        $suggestion = EventSuggestion::factory()->create(['processed' => false]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->postJson("/api/v1/admin/event-suggestions/{$suggestion->id}/accept", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'checkin_start', 'checkin_end']);
    }

    public function test_deny_suggestion_marks_processed_and_notifies_user(): void
    {
        Notification::fake();
        config(['services.telegram.admin.active' => false]);

        $suggestion = EventSuggestion::factory()->create(['processed' => false]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->postJson("/api/v1/admin/event-suggestions/{$suggestion->id}/deny", [
            'reason' => EventRejectionReason::DEFAULT->value,
        ])->assertNoContent();

        $this->assertDatabaseHas('event_suggestions', ['id' => $suggestion->id, 'processed' => true]);
        Notification::assertSentTo($suggestion->user, EventSuggestionProcessed::class);
    }

    public function test_deny_requires_valid_reason(): void
    {
        $suggestion = EventSuggestion::factory()->create(['processed' => false]);

        $this->actAsApiUserWithAllScopes($this->moderator);
        $this->postJson("/api/v1/admin/event-suggestions/{$suggestion->id}/deny", [
            'reason' => 'invalid-reason',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }
}
