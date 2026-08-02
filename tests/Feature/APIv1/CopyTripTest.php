<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\TripSource;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class CopyTripTest extends ApiTestCase
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
        $trip = $this->createProviderTrip();

        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertUnauthorized();
    }

    public function test_user_with_disallow_manual_trips_permission_is_forbidden(): void
    {
        $trip = $this->createProviderTrip();
        $this->user->givePermissionTo('disallow-manual-trips');

        Passport::actingAs($this->user->fresh(), ['*']);
        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertForbidden();
    }

    public function test_user_cannot_copy_a_manual_trip_of_another_user(): void
    {
        $trip = Trip::factory()->create([
            'source' => TripSource::USER,
            'user_id' => User::factory()->create()->id,
        ]);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertForbidden();
    }

    public function test_user_can_copy_their_own_manual_trip(): void
    {
        $trip = Trip::factory()->create([
            'source' => TripSource::USER,
            'user_id' => $this->user->id,
        ]);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/copy");

        $response->assertCreated();
        $this->assertNotSame($trip->uuid, $response->json('data.uuid'));
    }

    public function test_admin_cannot_copy_a_manual_trip_of_another_user(): void
    {
        $trip = Trip::factory()->create([
            'source' => TripSource::USER,
            'user_id' => User::factory()->create()->id,
        ]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actAsApiUserWithAllScopes($admin);
        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertForbidden();
    }

    public function test_unknown_trip_returns_not_found(): void
    {
        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson('/api/v1/trips/00000000-0000-4000-8000-000000000000/copy')->assertNotFound();
    }

    public function test_copy_creates_a_manual_trip_owned_by_the_user(): void
    {
        $trip = $this->createProviderTrip();

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/copy");

        $response->assertCreated();
        $copyUuid = $response->json('data.uuid');
        $this->assertNotSame($trip->uuid, $copyUuid);

        $copy = Trip::where('uuid', $copyUuid)->firstOrFail();
        $this->assertSame(TripSource::USER, $copy->source);
        $this->assertSame($this->user->id, $copy->user_id);
        $this->assertNotSame($trip->trip_id, $copy->trip_id);
    }

    public function test_copy_keeps_the_trip_data(): void
    {
        $trip = $this->createProviderTrip();

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/copy");

        $copy = Trip::where('uuid', $response->json('data.uuid'))->firstOrFail();

        $this->assertSame($trip->category, $copy->category);
        $this->assertSame($trip->linename, $copy->linename);
        $this->assertSame($trip->number, $copy->number);
        $this->assertSame($trip->journey_number, $copy->journey_number);
        $this->assertSame($trip->operator_id, $copy->operator_id);
        $this->assertSame($trip->origin_id, $copy->origin_id);
        $this->assertSame($trip->destination_id, $copy->destination_id);
        $this->assertSame($trip->polyline_id, $copy->polyline_id);
        $this->assertTrue($trip->departure->equalTo($copy->departure));
        $this->assertTrue($trip->arrival->equalTo($copy->arrival));
    }

    public function test_copy_duplicates_all_stopovers(): void
    {
        $trip = $this->createProviderTrip();
        $originalCount = $trip->stopovers->count();

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/copy");

        $copy = Trip::where('uuid', $response->json('data.uuid'))->firstOrFail();

        $this->assertCount($originalCount, $copy->stopovers);
        $this->assertSame(
            $trip->stopovers->pluck('train_station_id')->all(),
            $copy->stopovers->pluck('train_station_id')->all(),
        );
        // The original keeps its own stopovers
        $this->assertSame($originalCount, Stopover::where('trip_id', $trip->trip_id)->count());
    }

    public function test_own_checkin_is_moved_to_the_copy(): void
    {
        $trip = $this->createProviderTrip();
        $checkin = $this->checkinOnTrip($trip, $this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/copy");

        $copy = Trip::where('uuid', $response->json('data.uuid'))->firstOrFail();
        $checkin->refresh();

        $this->assertSame($copy->trip_id, $checkin->trip_id);
        $this->assertSame($copy->stopovers->first()->id, $checkin->origin_stopover_id);
        $this->assertSame($copy->stopovers->last()->id, $checkin->destination_stopover_id);
    }

    public function test_moved_checkin_loses_its_points_because_manual_trips_do_not_score(): void
    {
        $trip = $this->createProviderTrip();
        $checkin = $this->checkinOnTrip($trip, $this->user);
        $checkin->update(['points' => 42]);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertCreated();

        $checkin->refresh();
        $this->assertSame(0, $checkin->points);
    }

    public function test_checkin_of_another_user_keeps_its_points(): void
    {
        $trip = $this->createProviderTrip();
        $foreignCheckin = $this->checkinOnTrip($trip, User::factory()->create());
        $foreignCheckin->update(['points' => 42]);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertCreated();

        $foreignCheckin->refresh();
        $this->assertSame(42, $foreignCheckin->points);
    }

    public function test_checkins_of_other_users_stay_on_the_original_trip(): void
    {
        $trip = $this->createProviderTrip();
        $foreignCheckin = $this->checkinOnTrip($trip, User::factory()->create());

        $this->actAsApiUserWithAllScopes($this->user);
        $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->assertCreated();

        $foreignCheckin->refresh();
        $this->assertSame($trip->trip_id, $foreignCheckin->trip_id);
    }

    public function test_copied_trip_can_be_edited_by_the_user(): void
    {
        $trip = $this->createProviderTrip();

        $this->actAsApiUserWithAllScopes($this->user);
        $copyUuid = $this->postJson("/api/v1/trips/{$trip->uuid}/copy")->json('data.uuid');

        $this->putJson("/api/v1/trips/{$copyUuid}", ['lineName' => 'RE 99'])->assertNoContent();
        $this->assertDatabaseHas('hafas_trips', ['uuid' => $copyUuid, 'linename' => 'RE 99']);

        // ...while the provider trip it came from stays untouchable
        $this->putJson("/api/v1/trips/{$trip->uuid}", ['lineName' => 'RE 99'])->assertForbidden();
    }
}
