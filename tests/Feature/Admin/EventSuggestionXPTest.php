<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\ContributionHistory;
use App\Models\EventSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class EventSuggestionXPTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    private EventSuggestion $eventSuggestion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['contribution_xp' => 0, 'contribution_level' => 0]);
        $this->admin = User::factory()->create()->assignRole('admin');
        $this->eventSuggestion = EventSuggestion::factory(['user_id' => $this->user->id])->create();
    }

    private function acceptPayload(): array
    {
        return [
            'name'          => $this->eventSuggestion->name,
            'checkin_start' => $this->eventSuggestion->begin->toDateString(),
            'checkin_end'   => $this->eventSuggestion->end->toDateString(),
        ];
    }

    public function test_accept_suggestion_grants_five_xp(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/accept", $this->acceptPayload())
            ->assertCreated();

        $this->user->refresh();
        $this->assertEquals(5, $this->user->contribution_xp);

        $this->assertDatabaseCount('contribution_history', 1);
        $history = ContributionHistory::first();
        $this->assertEquals(5, $history->xp_change);
        $this->assertEquals('event_suggestion', $history->entity_type);
    }

    public function test_deny_with_duplicate_grants_zero_xp(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/deny", [
            'reason' => 'duplicate',
        ])->assertNoContent();

        $this->user->refresh();
        $this->assertEquals(0, $this->user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_deny_with_late_grants_zero_xp(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/deny", [
            'reason' => 'too-late',
        ])->assertNoContent();

        $this->user->refresh();
        $this->assertEquals(0, $this->user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_deny_with_default_grants_zero_xp(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/deny", [
            'reason' => 'denied',
        ])->assertNoContent();

        $this->user->refresh();
        $this->assertEquals(0, $this->user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_deny_with_not_applicable_subtracts_one_xp(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/deny", [
            'reason' => 'not-applicable',
        ])->assertNoContent();

        $this->user->refresh();
        $this->assertEquals(-1, $this->user->contribution_xp);

        $this->assertDatabaseCount('contribution_history', 1);
        $history = ContributionHistory::first();
        $this->assertEquals(-1, $history->xp_change);
    }

    public function test_deny_with_missing_information_subtracts_five_xp(): void
    {
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/deny", [
            'reason' => 'missing-information',
        ])->assertNoContent();

        $this->user->refresh();
        $this->assertEquals(-5, $this->user->contribution_xp);

        $this->assertDatabaseCount('contribution_history', 1);
        $history = ContributionHistory::first();
        $this->assertEquals(-5, $history->xp_change);
    }

    public function test_level_up_triggered_on_approval(): void
    {
        $this->user->update(['contribution_xp' => 48, 'contribution_level' => 0]);
        $this->actAsApiUserWithAllScopes($this->admin);

        $this->postJson("/api/v1/admin/event-suggestions/{$this->eventSuggestion->id}/accept", $this->acceptPayload())
            ->assertCreated();

        $this->user->refresh();
        $this->assertEquals(53, $this->user->contribution_xp);
        $this->assertEquals(1, $this->user->contribution_level);

        $history = ContributionHistory::first();
        $this->assertEquals(0, $history->level_before);
        $this->assertEquals(1, $history->level_after);
    }
}
