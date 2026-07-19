<?php

namespace Tests\Feature\APIv1;

use App\Enum\User\FriendCheckinSetting;
use App\Http\Controllers\Backend\User\FollowController;
use App\Models\Follow;
use App\Models\Status;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\YouHaveBeenCheckedIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Tests\ApiTestCase;

class FriendCheckinTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_can_checkin_themself(): void
    {
        // a little bit useless, but a user can always check in themselves somehow ⊂(◉‿◉)つ
        $user = User::factory()->create();
        $this->assertTrue(Gate::forUser($user)->allows('checkin', $user));
    }

    public function test_user_can_forbid_friend_checkins(): void
    {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::FORBIDDEN->value])->create();
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('checkin', $userToCheckin));
    }

    public function test_user_can_allow_checkins_for_friends(): void
    {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::FRIENDS->value])->create();
        $user = User::factory()->create();

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
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
                'with' => [
                    $userToCheckin->id,
                ],
            ],
        );
        $response->assertCreated();

        $original = Status::whereUserId($user->id);
        $this->assertEquals(1, $original->count());
        $original = $original->first();
        $this->assertNull($original->created_by_user_id);

        $created = Status::whereUserId($userToCheckin->id)->get();
        $this->assertEquals(1, $created->count());
        $created = $created->first();
        $this->assertEquals($user->id, $created->created_by_user_id);

        $this->assertDatabaseHas('train_checkins', ['user_id' => $user->id, 'trip_id' => $trip->trip_id]);
        $this->assertDatabaseHas('train_checkins', ['user_id' => $userToCheckin->id, 'trip_id' => $trip->trip_id]);
        $this->assertDatabaseCount('statuses', 2);

        $notification = $userToCheckin->refresh()->notifications->where('type', YouHaveBeenCheckedIn::class)->last();
        $this->assertStringContainsString($user->username, YouHaveBeenCheckedIn::getLead($notification->data));
        $this->assertStringContainsString($trip->originStation->name, YouHaveBeenCheckedIn::getNotice($notification->data));
        $this->assertStringContainsString($userToCheckin->statuses->last()->id, YouHaveBeenCheckedIn::getLink($notification->data));
    }

    public function test_user_can_allow_checkins_for_trusted_users(): void
    {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::LIST->value])->create();
        $user = User::factory()->create();

        $this->assertFalse(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));

        // Create a trusted relationship between the two users
        $this->actAsApiUserWithAllScopes($userToCheckin);
        $response = $this->postJson(
            uri: "/api/v1/user/{$userToCheckin->id}/trusted",
            data: ['userId' => $user->id]
        );
        $response->assertCreated();

        $this->assertTrue(Gate::forUser($user->fresh())->allows('checkin', $userToCheckin->fresh()));
    }

    public function test_user_cannot_checkin_more_then10_users(): void
    {
        $usersToCheckin = User::factory()->count(11)->create();
        $user = User::factory()->create();

        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->postJson(
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
                'with' => $usersToCheckin->pluck('id')->toArray(),
            ],
        );
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('with');
    }

    public function test_error_response_should_contain_forbidden_users(): void
    {
        $forbiddenUser = User::factory()->create(['friend_checkin' => FriendCheckinSetting::FORBIDDEN->value]);
        $allowedUser = User::factory()->create(['friend_checkin' => FriendCheckinSetting::FRIENDS->value]);
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        Follow::create(['user_id' => $user->id, 'follow_id' => $allowedUser->id]);
        Follow::create(['user_id' => $allowedUser->id, 'follow_id' => $user->id]);

        $trip = Trip::factory()->create();

        $response = $this->postJson(
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
                'with' => [
                    $forbiddenUser->id,
                    $allowedUser->id,
                ],
            ],
        );
        $response->assertStatus(403);
        $response->assertJsonStructure(['message', 'meta' => ['invalidUsers']]);
        $this->assertContains($forbiddenUser->id, $response->json('meta.invalidUsers'));
        $this->assertNotContains($allowedUser->id, $response->json('meta.invalidUsers'));
    }

    public function test_friend_checkin_stores_created_by_user_id(): void
    {
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::FRIENDS->value])->create();
        $user = User::factory()->create();

        // Create a follow relationship between the two users (following each other = friends)
        FollowController::createOrRequestFollow($user, $userToCheckin);
        FollowController::createOrRequestFollow($userToCheckin, $user);

        // check in both users
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->postJson(
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
                'with' => [
                    $userToCheckin->id,
                ],
            ],
        );
        $response->assertCreated();

        // Verify that the user who created the checkin is stored for the friend
        $this->assertDatabaseHas('statuses', [
            'user_id' => $userToCheckin->id,
            'created_by_user_id' => $user->id,
        ]);

        // Verify that the user's own checkin has NULL as created_by_user_id
        $this->assertDatabaseHas('statuses', [
            'user_id' => $user->id,
            'created_by_user_id' => null,
        ]);
    }

    public function test_friend_checkin_with_multiple_users_does_not_loop(): void
    {
        // Regression test for: checkin -> checkinOtherUsers -> checkin (loop) for multiple users
        Notification::fake();

        $userA = User::factory()->create();
        $userB = User::factory(['friend_checkin' => FriendCheckinSetting::FRIENDS->value])->create();
        $userC = User::factory(['friend_checkin' => FriendCheckinSetting::FRIENDS->value])->create();

        // A-B and A-C are mutual friends (required for friend_checkin = FRIENDS)
        FollowController::createOrRequestFollow($userA, $userB);
        FollowController::createOrRequestFollow($userB, $userA);
        FollowController::createOrRequestFollow($userA, $userC);
        FollowController::createOrRequestFollow($userC, $userA);

        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($userA);
        $response = $this->postJson(
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
                'with' => [$userB->id, $userC->id],
            ],
        );
        $response->assertCreated();

        // Exactly one checkin per user
        $this->assertDatabaseCount('train_checkins', 3);

        // All friends must be attributed to A, not to each other
        $this->assertDatabaseHas('statuses', ['user_id' => $userA->id, 'created_by_user_id' => null]);
        $this->assertDatabaseHas('statuses', ['user_id' => $userB->id, 'created_by_user_id' => $userA->id]);
        $this->assertDatabaseHas('statuses', ['user_id' => $userC->id, 'created_by_user_id' => $userA->id]);
    }

    public function test_including_self_in_with_does_not_create_self_referencing_created_by(): void
    {
        // Regression: a user putting their own id in `with` must not produce a status
        // where created_by_user_id == user_id. Their own checkin is the primary one (created_by null).
        $userToCheckin = User::factory(['friend_checkin' => FriendCheckinSetting::FRIENDS->value])->create();
        $user = User::factory()->create();

        FollowController::createOrRequestFollow($user, $userToCheckin);
        FollowController::createOrRequestFollow($userToCheckin, $user);

        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->postJson(
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
                'with' => [
                    $user->id, // the checking-in user includes themselves
                    $userToCheckin->id,
                ],
            ],
        );
        $response->assertCreated();

        // Exactly one status/checkin for the checking-in user, with created_by null
        $ownStatuses = Status::whereUserId($user->id)->get();
        $this->assertCount(1, $ownStatuses);
        $this->assertNull($ownStatuses->first()->created_by_user_id);

        // No status may reference itself as its own creator
        $this->assertSame(0, Status::whereColumn('created_by_user_id', 'user_id')->count());

        // The friend is still attributed to the checking-in user
        $this->assertDatabaseHas('statuses', [
            'user_id' => $userToCheckin->id,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function test_self_checkin_has_null_created_by_user_id(): void
    {
        $user = User::factory()->create();
        $trip = Trip::factory()->create();

        $this->actAsApiUserWithAllScopes($user);
        $response = $this->postJson(
            uri: '/api/v1/trains/checkin',
            data: [
                'tripId' => $trip->trip_id,
                'lineName' => $trip->linename,
                'start' => $trip->originStation->id,
                'departure' => $trip->departure,
                'destination' => $trip->destinationStation->id,
                'arrival' => $trip->arrival,
            ],
        );
        $response->assertCreated();

        // Verify that self-checkin has NULL as created_by_user_id
        $this->assertDatabaseHas('statuses', [
            'user_id' => $user->id,
            'created_by_user_id' => null,
        ]);
    }
}
