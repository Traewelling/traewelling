<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\ContributionHistory;
use App\Models\EventSuggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;

class EventSuggestionXPTest extends FeatureTestCase
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

    public function test_accept_suggestion_grants_five_xp(): void
    {
        $this->actingAs($this->admin);

        Http::fake(['/locations*' => Http::response([self::HANNOVER_HBF])]);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/accept', [
                'suggestionId' => $this->eventSuggestion->id,
                'name' => $this->eventSuggestion->name,
                'hashtag' => 'test',
                'host' => $this->eventSuggestion->host,
                'url' => 'https://example.com',
                'nearest_station_name' => 'Hannover Hbf',
                'begin' => $this->eventSuggestion->begin,
                'event_start' => $this->eventSuggestion->begin,
                'end' => $this->eventSuggestion->end,
                'event_end' => $this->eventSuggestion->end,
            ]);

        $this->user->refresh();
        $this->assertEquals(5, $this->user->contribution_xp);

        $this->assertDatabaseCount('contribution_history', 1);
        $history = ContributionHistory::first();
        $this->assertEquals(5, $history->xp_change);
        $this->assertEquals('event_suggestion', $history->entity_type);
    }

    public function test_deny_with_duplicate_grants_zero_xp(): void
    {
        $this->actingAs($this->admin);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/deny', [
                'id' => $this->eventSuggestion->id,
                'rejectionReason' => 'duplicate',
            ]);

        $this->user->refresh();
        $this->assertEquals(0, $this->user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_deny_with_late_grants_zero_xp(): void
    {
        $this->actingAs($this->admin);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/deny', [
                'id' => $this->eventSuggestion->id,
                'rejectionReason' => 'too-late',
            ]);

        $this->user->refresh();
        $this->assertEquals(0, $this->user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_deny_with_default_grants_zero_xp(): void
    {
        $this->actingAs($this->admin);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/deny', [
                'id' => $this->eventSuggestion->id,
                'rejectionReason' => 'denied',
            ]);

        $this->user->refresh();
        $this->assertEquals(0, $this->user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_deny_with_not_applicable_subtracts_one_xp(): void
    {
        $this->actingAs($this->admin);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/deny', [
                'id' => $this->eventSuggestion->id,
                'rejectionReason' => 'not-applicable',
            ]);

        $this->user->refresh();
        $this->assertEquals(-1, $this->user->contribution_xp);

        $this->assertDatabaseCount('contribution_history', 1);
        $history = ContributionHistory::first();
        $this->assertEquals(-1, $history->xp_change);
    }

    public function test_deny_with_missing_information_subtracts_five_xp(): void
    {
        $this->actingAs($this->admin);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/deny', [
                'id' => $this->eventSuggestion->id,
                'rejectionReason' => 'missing-information',
            ]);

        $this->user->refresh();
        $this->assertEquals(-5, $this->user->contribution_xp);

        $this->assertDatabaseCount('contribution_history', 1);
        $history = ContributionHistory::first();
        $this->assertEquals(-5, $history->xp_change);
    }

    public function test_level_up_triggered_on_approval(): void
    {
        $this->user->update(['contribution_xp' => 48, 'contribution_level' => 0]);
        $this->actingAs($this->admin);

        Http::fake(['/locations*' => Http::response([self::HANNOVER_HBF])]);

        $this->followingRedirects()
            ->post('/admin/events/suggestions/accept', [
                'suggestionId' => $this->eventSuggestion->id,
                'name' => $this->eventSuggestion->name,
                'hashtag' => 'test',
                'host' => $this->eventSuggestion->host,
                'url' => 'https://example.com',
                'nearest_station_name' => 'Hannover Hbf',
                'begin' => $this->eventSuggestion->begin,
                'event_start' => $this->eventSuggestion->begin,
                'end' => $this->eventSuggestion->end,
                'event_end' => $this->eventSuggestion->end,
            ]);

        $this->user->refresh();
        $this->assertEquals(53, $this->user->contribution_xp);
        $this->assertEquals(1, $this->user->contribution_level);

        $history = ContributionHistory::first();
        $this->assertEquals(0, $history->level_before);
        $this->assertEquals(1, $history->level_after);
    }
}
