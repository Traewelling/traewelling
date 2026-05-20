<?php

namespace Tests\Feature\Privacy;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\User\FollowController;
use App\Models\Checkin;
use App\Models\Follow;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StatusTagPrivacyTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_view_public_status_tag(): void
    {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::PUBLIC->value])->create();
        $randomUser = User::factory()->create();
        $this->assertTrue($randomUser->can('view', $statusTag));
    }

    public function test_user_view_private_status_tag(): void
    {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::PRIVATE])->create();
        $randomUser = User::factory()->create();
        $this->assertFalse($randomUser->can('view', $statusTag));
    }

    public function test_user_view_followers_only_status_tag(): void
    {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::FOLLOWERS])->create();
        $randomUser = User::factory()->create();
        $this->assertFalse($randomUser->can('view', $statusTag));
    }

    public function test_follower_view_followers_only_status_tag(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();

        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::FOLLOWERS])->create();
        $statusTag = StatusTag::factory([
            'status_id' => $status->id,
            'visibility' => StatusVisibility::FOLLOWERS,
        ])->create();

        FollowController::createOrRequestFollow($user, $follower);
        FollowController::createOrRequestFollow($follower, $user);
        $this->assertTrue($follower->refresh()->can('view', $statusTag));
    }

    public function test_follower_cant_view_private_status_tag(): void
    {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::PRIVATE])->create();
        $user = User::factory()->create();
        $follower = User::factory()->create();
        Follow::factory(['user_id' => $user->id, 'follow_id' => $follower->id])->create();
        $this->assertFalse($follower->can('view', $statusTag));
    }

    public function test_owner_can_view_private_status_tag(): void
    {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::PRIVATE])->create();
        $this->assertTrue($statusTag->status->user->can('view', $statusTag));
    }

    public function test_trusted_user_can_view_trusted_status_tag(): void
    {
        $user = User::factory()->create();
        $trustedUser = User::factory()->create();

        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::TRUSTED])->create();
        $statusTag = StatusTag::factory([
            'status_id' => $status->id,
            'visibility' => StatusVisibility::TRUSTED,
        ])->create();

        TrustedUser::create([
            'user_id' => $user->id,
            'trusted_id' => $trustedUser->id,
        ]);

        $this->assertTrue($trustedUser->can('view', $statusTag));
    }

    public function test_non_trusted_user_cant_view_trusted_status_tag(): void
    {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::TRUSTED])->create();
        $randomUser = User::factory()->create();
        $this->assertFalse($randomUser->can('view', $statusTag));
    }

    public function test_private_journey_number_tag_hidden_from_other_user_in_status_api(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $status = Status::factory(['user_id' => $owner->id, 'visibility' => StatusVisibility::PUBLIC])
            ->has(Checkin::factory()->state(fn () => ['user_id' => $owner->id]))
            ->create();

        StatusTag::factory([
            'status_id' => $status->id,
            'key' => StatusTagKey::JOURNEY_NUMBER->value,
            'value' => 'SECRET-123',
            'visibility' => StatusVisibility::PRIVATE,
        ])->create();

        Passport::actingAs($other, ['*']);
        $response = $this->getJson('/api/v1/status/' . $status->id);
        $response->assertOk();
        $response->assertJsonPath('data.checkin.manualJourneyNumber', null);
        $response->assertJsonMissingExact(['value' => 'SECRET-123']);
    }

    public function test_owner_sees_private_journey_number_tag_in_status_api(): void
    {
        $owner = User::factory()->create();

        $status = Status::factory(['user_id' => $owner->id, 'visibility' => StatusVisibility::PUBLIC])
            ->has(Checkin::factory()->state(fn () => ['user_id' => $owner->id]))
            ->create();

        StatusTag::factory([
            'status_id' => $status->id,
            'key' => StatusTagKey::JOURNEY_NUMBER->value,
            'value' => 'SECRET-123',
            'visibility' => StatusVisibility::PRIVATE,
        ])->create();

        Passport::actingAs($owner, ['*']);
        $response = $this->getJson('/api/v1/status/' . $status->id);
        $response->assertOk();
        $response->assertJsonPath('data.checkin.manualJourneyNumber', 'SECRET-123');
    }
}
