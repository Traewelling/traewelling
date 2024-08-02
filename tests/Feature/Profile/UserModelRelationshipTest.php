<?php

namespace Tests\Feature\Profile;

use App\Http\Controllers\Backend\User\FollowController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class UserModelRelationshipTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testFollowersRelationship(): void {
        $user     = User::factory()->create();
        $follower = User::factory()->create();
        FollowController::createOrRequestFollow($follower, $user);

        $user->refresh();

        $this->assertCount(1, $user->userFollowers);
        $this->assertEquals($follower->id, $user->userFollowers->first()->id);
    }

    public function testFollowingRelationship(): void {
        $user      = User::factory()->create();
        $following = User::factory()->create();
        FollowController::createOrRequestFollow($user, $following);

        $user->refresh();

        $this->assertCount(1, $user->userFollowings);
        $this->assertEquals($following->id, $user->userFollowings->first()->id);
    }
}
