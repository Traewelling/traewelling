<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\Checkin;
use App\Models\Trip;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class TripStatusesTest extends ApiTestCase
{
    use RefreshDatabase;

    private function url(int $tripId): string
    {
        return "/api/v1/trips/{$tripId}/statuses";
    }

    private function checkinOnTrip(Trip $trip, User $user, StatusVisibility $visibility = StatusVisibility::PUBLIC): Checkin
    {
        $checkin = Checkin::factory([
            'user_id' => $user->id,
            'trip_id' => $trip->trip_id,
            'departure' => $trip->departure,
            'arrival' => $trip->arrival,
            'origin_stopover_id' => $trip->stopovers->where('train_station_id', $trip->originStation->id)->first()->id,
            'destination_stopover_id' => $trip->stopovers->where('train_station_id', $trip->destinationStation->id)->first()->id,
        ])->create();

        $checkin->status->update([
            'user_id' => $user->id,
            'visibility' => $visibility->value,
        ]);

        return $checkin->fresh();
    }

    public function test_returns_404_for_unknown_trip(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $this->getJson($this->url(999999))->assertNotFound();
    }

    public function test_unauthenticated_user_can_access_endpoint(): void
    {
        $trip = Trip::factory()->create();

        $this->getJson($this->url($trip->id))->assertOk();
    }

    public function test_returns_public_statuses_for_authenticated_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $bob->id]);
    }

    public function test_unauthenticated_user_sees_public_statuses(): void
    {
        $bob = User::factory()->create();
        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_unauthenticated_user_does_not_see_private_visibility(): void
    {
        $bob = User::factory()->create();
        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PRIVATE);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_user_does_not_see_followers_only_statuses(): void
    {
        $bob = User::factory()->create();
        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::FOLLOWERS);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_unauthenticated_user_does_not_see_statuses_from_private_profile(): void
    {
        $bob = User::factory(['private_profile' => true])->create();
        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_authenticated_user_does_not_see_private_status_of_other_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PRIVATE);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_user_always_sees_own_private_status(): void
    {
        $alice = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $alice, StatusVisibility::PRIVATE);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_followers_only_status_is_visible_to_follower(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // Alice follows Bob
        UserBackend::createFollow($alice, $bob, isApprovedRequest: true);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::FOLLOWERS);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_followers_only_status_is_not_visible_to_non_follower(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::FOLLOWERS);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_trusted_only_status_is_visible_to_trusted_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // Bob trusts Alice
        TrustedUser::create(['user_id' => $bob->id, 'trusted_id' => $alice->id]);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::TRUSTED);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_trusted_only_status_is_not_visible_to_untrusted_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::TRUSTED);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_authenticated_status_is_not_visible_to_unauthenticated_user(): void
    {
        $bob = User::factory()->create();
        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::AUTHENTICATED);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_authenticated_status_is_visible_to_authenticated_user(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::AUTHENTICATED);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_blocked_users_statuses_are_not_visible(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // Alice blocks Bob
        UserController::blockUser($alice, $bob);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
        $response->assertJsonMissing(['id' => $bob->id]);
    }

    public function test_status_not_visible_when_requester_is_blocked_by_owner(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // Bob blocks Alice
        UserController::blockUser($bob, $alice);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_returns_all_visible_statuses_from_multiple_users(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $carol = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $alice, StatusVisibility::PRIVATE);  // own → visible
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);      // public → visible
        $this->checkinOnTrip($trip, $carol, StatusVisibility::PRIVATE);   // other's private → hidden

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }

    public function test_response_contains_expected_status_structure(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $trip = Trip::factory()->create();
        $this->checkinOnTrip($trip, $bob, StatusVisibility::PUBLIC);

        $response = $this->getJson($this->url($trip->id));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'body',
                    'visibility',
                    'user' => ['id', 'displayName', 'username', 'profilePicture'],
                    'checkin' => [
                        'origin' => ['id', 'name'],
                        'destination' => ['id', 'name'],
                    ],
                    'createdAt',
                ],
            ],
        ]);
    }
}
