<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Enum\StatusVisibility;
use App\Models\Checkin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class HideStatusTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_hides_old_statuses_beyond_cutoff(): void
    {
        $user = User::factory()->create(['privacy_hide_days' => 7]);

        $oldCheckin = Checkin::factory(['user_id' => $user->id])->create();
        $oldCheckin->destinationStopover->update([
            'arrival_planned' => now()->subDays(10),
        ]);
        $oldCheckin->status->update(['visibility' => StatusVisibility::PUBLIC]);

        $recentCheckin = Checkin::factory(['user_id' => $user->id])->create();
        $recentCheckin->destinationStopover->update([
            'arrival_planned' => now()->subDays(3),
        ]);
        $recentCheckin->status->update(['visibility' => StatusVisibility::PUBLIC]);

        $this->artisan('trwl:hideStatus')->assertExitCode(0);

        $this->assertDatabaseHas('statuses', [
            'id' => $oldCheckin->status_id,
            'visibility' => StatusVisibility::PRIVATE->value,
        ]);
        $this->assertDatabaseHas('statuses', [
            'id' => $recentCheckin->status_id,
            'visibility' => StatusVisibility::PUBLIC->value,
        ]);
    }

    public function test_already_private_statuses_are_not_touched(): void
    {
        $user = User::factory()->create(['privacy_hide_days' => 7]);

        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $checkin->destinationStopover->update([
            'arrival_planned' => now()->subDays(30),
        ]);
        $checkin->status->update(['visibility' => StatusVisibility::PRIVATE]);

        $updatedAtBefore = $checkin->status->fresh()->updated_at;

        $this->artisan('trwl:hideStatus')->assertExitCode(0);

        $this->assertDatabaseHas('statuses', [
            'id' => $checkin->status_id,
            'visibility' => StatusVisibility::PRIVATE->value,
        ]);
        $this->assertEquals($updatedAtBefore, $checkin->status->fresh()->updated_at);
    }

    public function test_hides_statuses_at_maximum_hide_days(): void
    {
        $user = User::factory()->create(['privacy_hide_days' => 365]);

        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $checkin->destinationStopover->update([
            'arrival_planned' => now()->subDays(366),
        ]);
        $checkin->status->update(['visibility' => StatusVisibility::PUBLIC]);

        $this->artisan('trwl:hideStatus')->assertExitCode(0);

        $this->assertDatabaseHas('statuses', [
            'id' => $checkin->status_id,
            'visibility' => StatusVisibility::PRIVATE->value,
        ]);
    }

    public function test_does_nothing_when_no_users_have_privacy_hide_days(): void
    {
        $user = User::factory()->create(['privacy_hide_days' => null]);

        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $checkin->destinationStopover->update([
            'arrival_planned' => now()->subDays(30),
        ]);
        $checkin->status->update(['visibility' => StatusVisibility::PUBLIC]);

        $this->artisan('trwl:hideStatus')->assertExitCode(0);

        $this->assertDatabaseHas('statuses', [
            'id' => $checkin->status_id,
            'visibility' => StatusVisibility::PUBLIC->value,
        ]);
    }

    public function test_only_affects_own_user_statuses(): void
    {
        $user = User::factory()->create(['privacy_hide_days' => 7]);
        $otherUser = User::factory()->create(['privacy_hide_days' => null]);

        $ownCheckin = Checkin::factory(['user_id' => $user->id])->create();
        $ownCheckin->destinationStopover->update(['arrival_planned' => now()->subDays(10)]);
        $ownCheckin->status->update(['visibility' => StatusVisibility::PUBLIC]);

        $otherCheckin = Checkin::factory(['user_id' => $otherUser->id])->create();
        $otherCheckin->destinationStopover->update(['arrival_planned' => now()->subDays(10)]);
        $otherCheckin->status->update([
            'user_id' => $otherUser->id,
            'visibility' => StatusVisibility::PUBLIC,
        ]);

        $this->artisan('trwl:hideStatus')->assertExitCode(0);

        $this->assertDatabaseHas('statuses', [
            'id' => $ownCheckin->status_id,
            'visibility' => StatusVisibility::PRIVATE->value,
        ]);
        $this->assertDatabaseHas('statuses', [
            'id' => $otherCheckin->status_id,
            'visibility' => StatusVisibility::PUBLIC->value,
        ]);
    }
}
