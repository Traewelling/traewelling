<?php

namespace Feature\Privacy;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\User\FollowController;
use App\Models\Follow;
use App\Models\Status;
use App\Models\StatusTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class StatusTagPrivacyTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserViewPublicStatusTag(): void {
        $statusTag  = StatusTag::factory(['visibility' => StatusVisibility::PUBLIC->value])->create();
        $randomUser = User::factory()->create();
        $this->assertTrue($randomUser->can('view', $statusTag));
    }

    public function testUserViewPrivateStatusTag(): void {
        $statusTag  = StatusTag::factory(['visibility' => StatusVisibility::PRIVATE])->create();
        $randomUser = User::factory()->create();
        $this->assertFalse($randomUser->can('view', $statusTag));
    }

    public function testUserViewFollowersOnlyStatusTag(): void {
        $statusTag  = StatusTag::factory(['visibility' => StatusVisibility::FOLLOWERS])->create();
        $randomUser = User::factory()->create();
        $this->assertFalse($randomUser->can('view', $statusTag));
    }

    public function testFollowerViewFollowersOnlyStatusTag(): void {
        $user      = User::factory()->create();
        $follower  = User::factory()->create();

        $status    = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::FOLLOWERS])->create();
        $statusTag = StatusTag::factory([
                                            'status_id'  => $status->id,
                                            'visibility' => StatusVisibility::FOLLOWERS,
                                        ])->create();

        FollowController::createOrRequestFollow($user, $follower);
        FollowController::createOrRequestFollow($follower, $user);
        $this->assertTrue($follower->refresh()->can('view', $statusTag));
    }

    public function testFollowerCantViewPrivateStatusTag(): void {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::PRIVATE])->create();
        $user      = User::factory()->create();
        $follower  = User::factory()->create();
        Follow::factory(['user_id' => $user->id, 'follow_id' => $follower->id])->create();
        $this->assertFalse($follower->can('view', $statusTag));
    }

    public function testOwnerCanViewPrivateStatusTag(): void {
        $statusTag = StatusTag::factory(['visibility' => StatusVisibility::PRIVATE])->create();
        $this->assertTrue($statusTag->status->user->can('view', $statusTag));
    }
}
