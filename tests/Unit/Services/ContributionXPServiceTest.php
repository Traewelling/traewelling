<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enum\ContributionActionType;
use App\Models\ContributionHistory;
use App\Models\User;
use App\Services\Contribution\ContributionXPService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Unit\UnitTestCase;

class ContributionXPServiceTest extends UnitTestCase
{
    use RefreshDatabase;

    public function test_grant_xp_adds_positive_xp(): void
    {
        $user = User::factory()->create(['contribution_xp' => 0, 'contribution_level' => 0]);

        ContributionXPService::grantXP(
            user: $user,
            xpChange: 5,
            action: ContributionActionType::EVENT_SUGGESTED,
            entityType: 'event_suggestion',
            entityId: 1,
            note: 'Event approved',
        );

        $user->refresh();
        $this->assertEquals(5, $user->contribution_xp);
        $this->assertEquals(0, $user->contribution_level);
    }

    public function test_grant_xp_subtracts_negative_xp(): void
    {
        $user = User::factory()->create(['contribution_xp' => 10, 'contribution_level' => 0]);

        ContributionXPService::grantXP(
            user: $user,
            xpChange: -5,
            action: ContributionActionType::EVENT_SUGGESTED,
            entityType: 'event_suggestion',
            entityId: 1,
            note: 'Event denied: missing-information',
        );

        $user->refresh();
        $this->assertEquals(5, $user->contribution_xp);
    }

    public function test_grant_xp_allows_negative_total(): void
    {
        $user = User::factory()->create(['contribution_xp' => 0, 'contribution_level' => 0]);

        ContributionXPService::grantXP(
            user: $user,
            xpChange: -5,
            action: ContributionActionType::EVENT_SUGGESTED,
            entityType: 'event_suggestion',
            entityId: 1,
        );

        $user->refresh();
        $this->assertEquals(-5, $user->contribution_xp);
        $this->assertEquals(0, $user->contribution_level);
    }

    public function test_grant_xp_does_nothing_when_zero(): void
    {
        $user = User::factory()->create(['contribution_xp' => 10, 'contribution_level' => 0]);

        ContributionXPService::grantXP(
            user: $user,
            xpChange: 0,
            action: ContributionActionType::EVENT_SUGGESTED,
            entityType: 'event_suggestion',
            entityId: 1,
        );

        $user->refresh();
        $this->assertEquals(10, $user->contribution_xp);
        $this->assertDatabaseCount('contribution_history', 0);
    }

    public function test_grant_xp_creates_history_entry(): void
    {
        $user = User::factory()->create(['contribution_xp' => 0, 'contribution_level' => 0]);

        ContributionXPService::grantXP(
            user: $user,
            xpChange: 5,
            action: ContributionActionType::EVENT_SUGGESTED,
            entityType: 'event_suggestion',
            entityId: 42,
            note: 'Test note',
        );

        $this->assertDatabaseCount('contribution_history', 1);

        $history = ContributionHistory::first();
        $this->assertEquals($user->id, $history->user_id);
        $this->assertEquals(ContributionActionType::EVENT_SUGGESTED, $history->action_type);
        $this->assertEquals('event_suggestion', $history->entity_type);
        $this->assertEquals(42, $history->entity_id);
        $this->assertEquals(5, $history->xp_change);
        $this->assertEquals(0, $history->level_before);
        $this->assertEquals(0, $history->level_after);
        $this->assertEquals('Test note', $history->note);
    }

    public function test_grant_xp_updates_level_on_threshold(): void
    {
        $user = User::factory()->create(['contribution_xp' => 48, 'contribution_level' => 0]);

        ContributionXPService::grantXP(
            user: $user,
            xpChange: 5,
            action: ContributionActionType::EVENT_SUGGESTED,
            entityType: 'event_suggestion',
            entityId: 1,
        );

        $user->refresh();
        $this->assertEquals(53, $user->contribution_xp);
        $this->assertEquals(1, $user->contribution_level);

        $history = ContributionHistory::first();
        $this->assertEquals(0, $history->level_before);
        $this->assertEquals(1, $history->level_after);
    }

    public function test_get_xp_for_event_approval(): void
    {
        $this->assertEquals(5, ContributionXPService::getXPForEventApproval());
    }
}
