<?php

namespace Tests\Feature\APIv1;

use App\Enum\User\FriendCheckinSetting;
use App\Http\Controllers\Backend\User\FollowController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\ApiTestCase;

class FriendCheckinTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCanCheckinThemself(): void {
        // a little bit useless, but a user can always check in themselves somehow ⊂(◉‿◉)つ
        $user = User::factory()->create();
        $this->assertTrue(Gate::forUser($user)->allows('checkin', $user));
    }

    public function testUserCanForbidFriendCheckins(): void {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::FORBIDDEN->value])->create();
        $user          = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('checkin', $userToCheckin));
    }

    public function testUserCanAllowCheckinsForFriends(): void {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::FRIENDS->value])->create();
        $user          = User::factory()->create();

        $this->assertFalse(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));

        // Create a follow relationship between the two users (following each other = friends)
        FollowController::createOrRequestFollow($user, $userToCheckin);
        FollowController::createOrRequestFollow($userToCheckin, $user);

        $this->assertTrue(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));
    }

    public function testUserCanAllowCheckinsForTrustedUsers(): void {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::LIST->value])->create();
        $user          = User::factory()->create();

        $this->assertFalse(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));

        // Create a trusted relationship between the two users
        // TODO: use backend function to create trusted relationship
        $userToCheckin->trustedUsers()->attach($user);

        $this->assertTrue(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));
    }
}

