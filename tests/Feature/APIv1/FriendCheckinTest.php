<?php

namespace Tests\Feature\APIv1;

use App\Enum\User\FriendCheckinSetting;
use App\Http\Controllers\Backend\User\FollowController;
use App\Models\Follow;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\YouHaveBeenCheckedIn;
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

        $this->assertFalse(Gate::forUser($user->refresh())->allows('checkin', $userToCheckin->refresh()));

        // Create a follow relationship between the two users (following each other = friends)
        FollowController::createOrRequestFollow($user, $userToCheckin);
        FollowController::createOrRequestFollow($userToCheckin, $user);

        $this->assertTrue(Gate::forUser($user->refresh())->allows('checkin', $userToCheckin->refresh()));

        // check that there are currently no checkins
        $this->assertDatabaseCount('train_checkins', 0);

        // check in both users
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->postJson(
            uri:  '/api/v1/trains/checkin',
            data: [
                      'tripId'      => $trip->trip_id,
                      'lineName'    => $trip->linename,
                      'start'       => $trip->originStation->id,
                      'departure'   => $trip->departure,
                      'destination' => $trip->destinationStation->id,
                      'arrival'     => $trip->arrival,
                      'with'        => [
                          $userToCheckin->id
                      ]
                  ],
        );
        $response->assertCreated();

        $this->assertDatabaseHas('train_checkins', ['user_id' => $user->id, 'trip_id' => $trip->trip_id]);
        $this->assertDatabaseHas('train_checkins', ['user_id' => $userToCheckin->id, 'trip_id' => $trip->trip_id]);

        $notification = $userToCheckin->refresh()->notifications->where('type', YouHaveBeenCheckedIn::class)->last();
        $this->assertStringContainsString($user->username, YouHaveBeenCheckedIn::getLead($notification->data));
        $this->assertStringContainsString($trip->originStation->name, YouHaveBeenCheckedIn::getNotice($notification->data));
        $this->assertStringContainsString($userToCheckin->statuses->last()->id, YouHaveBeenCheckedIn::getLink($notification->data));
    }

    public function testUserCanAllowCheckinsForTrustedUsers(): void {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::LIST->value])->create();
        $user          = User::factory()->create();

        $this->assertFalse(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));

        // Create a trusted relationship between the two users
        $this->actAsApiUserWithAllScopes($userToCheckin);
        $response = $this->postJson(
            uri:  "/api/v1/user/{$userToCheckin->id}/trusted",
            data: ['userId' => $user->id]
        );
        $response->assertCreated();

        $this->assertTrue(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));
    }

    public function testUserCannotCheckinMoreThen10Users(): void {
        $usersToCheckin = User::factory()->count(11)->create();
        $user           = User::factory()->create();

        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->postJson(
            uri:  '/api/v1/trains/checkin',
            data: [
                      'tripId'      => $trip->trip_id,
                      'lineName'    => $trip->linename,
                      'start'       => $trip->originStation->id,
                      'departure'   => $trip->departure,
                      'destination' => $trip->destinationStation->id,
                      'arrival'     => $trip->arrival,
                      'with'        => $usersToCheckin->pluck('id')->toArray()
                  ],
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('with');
    }

    public function testErrorResponseShouldContainForbiddenUsers(): void {
        $forbiddenUser = User::factory()->create(['friend_checkin' => FriendCheckinSetting::FORBIDDEN->value]);
        $allowedUser   = User::factory()->create(['friend_checkin' => FriendCheckinSetting::FRIENDS->value]);
        $user          = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        Follow::create(['user_id' => $user->id, 'follow_id' => $allowedUser->id]);
        Follow::create(['user_id' => $allowedUser->id, 'follow_id' => $user->id]);

        $trip = Trip::factory()->create();

        $response = $this->postJson(
            uri:  '/api/v1/trains/checkin',
            data: [
                      'tripId'      => $trip->trip_id,
                      'lineName'    => $trip->linename,
                      'start'       => $trip->originStation->id,
                      'departure'   => $trip->departure,
                      'destination' => $trip->destinationStation->id,
                      'arrival'     => $trip->arrival,
                      'with'        => [
                          $forbiddenUser->id,
                          $allowedUser->id
                      ]
                  ],
        );
        $response->assertStatus(403);
        $response->assertJsonStructure(['message', 'meta' => ['invalidUsers']]);
        $this->assertContains($forbiddenUser->id, $response->json('meta.invalidUsers'));
        $this->assertNotContains($allowedUser->id, $response->json('meta.invalidUsers'));
    }
}

