<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Stopover;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class DeleteTripTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertUnauthorized();
    }

    public function test_user_cannot_delete_a_trip_of_another_user(): void
    {
        $trip = $this->createManualTrip(User::factory()->create());

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertForbidden();

        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id]);
    }

    public function test_user_cannot_delete_a_trip_from_a_data_provider(): void
    {
        $trip = $this->createProviderTrip();

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertForbidden();

        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id]);
    }

    public function test_unknown_trip_returns_not_found(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson('/api/v1/trips/00000000-0000-4000-8000-000000000000')->assertNotFound();
    }

    public function test_user_can_delete_an_own_trip_without_checkins(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertNoContent();

        $this->assertDatabaseMissing('hafas_trips', ['id' => $trip->id]);
        $this->assertSame(0, Stopover::where('trip_id', $trip->trip_id)->count());
    }

    public function test_trip_with_an_own_checkin_cannot_be_deleted(): void
    {
        $trip = $this->createManualTrip($this->user);
        $this->checkinOnTrip($trip, $this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->deleteJson("/api/v1/trips/{$trip->uuid}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id]);
    }

    public function test_trip_with_a_checkin_of_another_user_cannot_be_deleted(): void
    {
        $trip = $this->createManualTrip($this->user);
        $this->checkinOnTrip($trip, User::factory()->create());

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertStatus(409);

        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id]);
    }

    public function test_trip_becomes_deletable_once_the_last_checkin_is_gone(): void
    {
        $trip = $this->createManualTrip($this->user);
        $checkin = $this->checkinOnTrip($trip, $this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertStatus(409);

        $checkin->delete();

        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertNoContent();
        $this->assertDatabaseMissing('hafas_trips', ['id' => $trip->id]);
    }

    public function test_admin_can_delete_a_foreign_trip_without_checkins(): void
    {
        $trip = $this->createManualTrip(User::factory()->create());
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actAsApiUserWithAllScopes($admin);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}")->assertNoContent();

        $this->assertDatabaseMissing('hafas_trips', ['id' => $trip->id]);
    }

    public function test_index_reports_the_number_of_checkins_on_each_trip(): void
    {
        $trip = $this->createManualTrip($this->user);
        $this->checkinOnTrip($trip, $this->user);
        $this->checkinOnTrip($trip, User::factory()->create());

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->getJson('/api/v1/trips');

        $response->assertOk();
        $response->assertJsonPath('data.0.uuid', $trip->uuid);
        $response->assertJsonPath('data.0.checkinCount', 2);
    }

    public function test_checkin_count_is_zero_for_a_trip_nobody_is_checked_into(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->getJson("/api/v1/trips/{$trip->uuid}")->assertJsonPath('data.checkinCount', 0);
    }
}
