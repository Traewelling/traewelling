<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\TripSource;
use App\Models\Checkin;
use App\Models\Operator;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class UpdateTripTest extends ApiTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function firstStopover(Trip $trip): Stopover
    {
        return $trip->stopovers()->first();
    }

    private function lastStopover(Trip $trip): Stopover
    {
        return $trip->stopovers->last();
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->putJson("/api/v1/trips/{$trip->uuid}", ['lineName' => 'RE 2'])->assertUnauthorized();
    }

    public function test_user_cannot_update_foreign_trip(): void
    {
        $trip = $this->createManualTrip(User::factory()->create());

        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson("/api/v1/trips/{$trip->uuid}", ['lineName' => 'RE 2'])->assertForbidden();
    }

    public function test_user_cannot_update_trip_from_a_data_provider(): void
    {
        $trip = Trip::factory()->create(['source' => TripSource::TRANSITOUS, 'user_id' => $this->user->id]);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson("/api/v1/trips/{$trip->uuid}", ['lineName' => 'RE 2'])->assertForbidden();
    }

    public function test_user_with_disallow_manual_trips_permission_is_forbidden(): void
    {
        $trip = $this->createManualTrip($this->user);
        $this->user->givePermissionTo('disallow-manual-trips');

        Passport::actingAs($this->user->fresh(), ['*']);
        $this->putJson("/api/v1/trips/{$trip->uuid}", ['lineName' => 'RE 2'])->assertForbidden();
    }

    public function test_admin_can_update_a_foreign_trip_from_a_data_provider(): void
    {
        $trip = Trip::factory()->create(['source' => TripSource::TRANSITOUS]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actAsApiUserWithAllScopes($admin);
        $this->putJson("/api/v1/trips/{$trip->uuid}", ['lineName' => 'RE 2'])->assertNoContent();

        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id, 'linename' => 'RE 2']);
    }

    public function test_admin_can_edit_stopovers_of_a_foreign_trip(): void
    {
        $trip = Trip::factory()->create(['source' => TripSource::TRANSITOUS]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $stopover = $this->firstStopover($trip);

        $this->actAsApiUserWithAllScopes($admin);
        $this->putJson("/api/v1/trips/{$trip->uuid}/stopovers/{$stopover->uuid}", [
            'departurePlatformPlanned' => '12',
        ])->assertNoContent();

        $this->assertDatabaseHas('train_stopovers', ['id' => $stopover->id, 'departure_platform_planned' => '12']);
    }

    public function test_unauthenticated_cannot_list_trips(): void
    {
        $this->getJson('/api/v1/trips')->assertUnauthorized();
    }

    public function test_index_returns_only_own_manual_trips(): void
    {
        $ownTrip = $this->createManualTrip($this->user);
        $foreignTrip = $this->createManualTrip(User::factory()->create());
        $providerTrip = Trip::factory()->create([
            'source' => TripSource::TRANSITOUS,
            'user_id' => $this->user->id,
        ]);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->getJson('/api/v1/trips');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.uuid', $ownTrip->uuid);
        $response->assertJsonMissing(['uuid' => $foreignTrip->uuid]);
        $response->assertJsonMissing(['uuid' => $providerTrip->uuid]);
    }

    public function test_index_is_cursor_paginated_and_sorted_by_departure_desc(): void
    {
        $older = $this->createManualTrip($this->user);
        $older->update(['departure' => $older->departure->clone()->subDays(2)]);
        $newer = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->getJson('/api/v1/trips');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'links' => ['next'], 'meta' => ['next_cursor']]);
        $response->assertJsonPath('data.0.uuid', $newer->uuid);
        $response->assertJsonPath('data.1.uuid', $older->uuid);
    }

    public function test_user_can_show_own_trip_with_uuids(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->getJson("/api/v1/trips/{$trip->uuid}");

        $response->assertOk();
        $response->assertJsonPath('data.uuid', $trip->uuid);
        $response->assertJsonPath('data.stopovers.0.uuid', $this->firstStopover($trip)->uuid);
    }

    public function test_user_can_update_trip_metadata(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->putJson("/api/v1/trips/{$trip->uuid}", [
            'category' => 'tram',
            'lineName' => 'STR 5',
            'journeyNumber' => 4711,
        ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('hafas_trips', [
            'id' => $trip->id,
            'linename' => 'STR 5',
            'number' => 'STR 5',
            'category' => 'tram',
            'journey_number' => 4711,
        ]);
    }

    public function test_user_can_change_and_remove_the_operator(): void
    {
        $trip = $this->createManualTrip($this->user);
        $operator = Operator::factory()->create();

        $this->actAsApiUserWithAllScopes($this->user);

        $this->putJson("/api/v1/trips/{$trip->uuid}", ['operatorUuid' => $operator->id])->assertNoContent();
        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id, 'operator_id' => $operator->id]);

        $this->putJson("/api/v1/trips/{$trip->uuid}", ['operatorUuid' => null])->assertNoContent();
        $this->assertDatabaseHas('hafas_trips', ['id' => $trip->id, 'operator_id' => null]);
    }

    public function test_operator_legacy_id_is_not_accepted(): void
    {
        $trip = $this->createManualTrip($this->user);
        $operator = Operator::factory()->create(['legacy_id' => 42]);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->putJson("/api/v1/trips/{$trip->uuid}", ['operatorUuid' => (string) $operator->legacy_id]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['operatorUuid']);
    }

    public function test_unknown_operator_uuid_is_rejected(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->putJson("/api/v1/trips/{$trip->uuid}", [
            'operatorUuid' => '00000000-0000-4000-8000-000000000000',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['operatorUuid']);
    }

    public function test_adding_a_stopover_after_the_destination_moves_the_trip_destination(): void
    {
        $trip = $this->createManualTrip($this->user);
        $newStation = Station::factory()->create();
        $newArrival = $trip->arrival->clone()->addMinutes(20);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/stopovers", [
            'stationUuid' => $newStation->uuid,
            'arrivalPlanned' => $newArrival->toIso8601String(),
        ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('train_stopovers', [
            'trip_id' => $trip->trip_id,
            'train_station_id' => $newStation->id,
        ]);

        $trip->refresh();
        $this->assertSame($newStation->id, $trip->destination_id);
        $this->assertTrue($newArrival->equalTo($trip->arrival));
    }

    public function test_adding_a_stopover_requires_a_planned_time(): void
    {
        $trip = $this->createManualTrip($this->user);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->postJson("/api/v1/trips/{$trip->uuid}/stopovers", [
            'stationUuid' => Station::factory()->create()->uuid,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['arrivalPlanned', 'departurePlanned']);
    }

    public function test_user_can_update_a_stopover_and_the_trip_departure_follows(): void
    {
        $trip = $this->createManualTrip($this->user);
        $stopover = $this->firstStopover($trip);
        $newTime = $trip->departure->clone()->subMinutes(10);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->putJson("/api/v1/trips/{$trip->uuid}/stopovers/{$stopover->uuid}", [
            'arrivalPlanned' => $newTime->toIso8601String(),
            'departurePlanned' => $newTime->toIso8601String(),
            'departurePlatformPlanned' => '7a',
        ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('train_stopovers', [
            'id' => $stopover->id,
            'departure_platform_planned' => '7a',
        ]);

        $trip->refresh();
        $this->assertTrue($newTime->equalTo($trip->departure));
    }

    public function test_changing_the_station_of_a_stopover_resets_the_routing(): void
    {
        $trip = $this->createManualTrip($this->user);
        $stopover = $this->firstStopover($trip);
        $newStation = Station::factory()->create();

        $this->assertNotNull($trip->polyline_id);

        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson("/api/v1/trips/{$trip->uuid}/stopovers/{$stopover->uuid}", [
            'stationUuid' => $newStation->uuid,
        ])->assertNoContent();

        $trip->refresh();
        $this->assertNull($trip->polyline_id);
        $this->assertSame($newStation->id, $trip->origin_id);
    }

    public function test_departure_before_arrival_is_rejected_and_rolled_back(): void
    {
        $trip = $this->createManualTrip($this->user);
        $stopover = $this->lastStopover($trip);
        $originalArrival = $stopover->arrival_planned;

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->putJson("/api/v1/trips/{$trip->uuid}/stopovers/{$stopover->uuid}", [
            'arrivalPlanned' => $stopover->arrival_planned->clone()->toIso8601String(),
            'departurePlanned' => $stopover->arrival_planned->clone()->subMinutes(5)->toIso8601String(),
        ]);

        $response->assertStatus(422);
        $this->assertTrue($originalArrival->equalTo($stopover->fresh()->arrival_planned));
    }

    public function test_moving_a_stopover_past_a_checkin_destination_is_rejected(): void
    {
        $trip = $this->createManualTrip($this->user);
        $checkin = $this->createCheckinOnTrip($trip);
        $originStopover = $checkin->originStopover;

        // Moving the origin behind the destination would reverse the checkin
        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->putJson("/api/v1/trips/{$trip->uuid}/stopovers/{$originStopover->uuid}", [
            'arrivalPlanned' => $trip->arrival->clone()->addHour()->toIso8601String(),
            'departurePlanned' => $trip->arrival->clone()->addHour()->toIso8601String(),
        ]);

        $response->assertStatus(422);
        $this->assertTrue($originStopover->arrival_planned->equalTo($originStopover->fresh()->arrival_planned));
    }

    public function test_user_can_delete_a_stopover(): void
    {
        $trip = $this->createManualTrip($this->user);
        $stopover = $trip->stopovers[1];

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/{$stopover->uuid}")->assertNoContent();

        $this->assertDatabaseMissing('train_stopovers', ['id' => $stopover->id]);
    }

    public function test_deleting_a_stopover_referenced_by_a_checkin_is_rejected(): void
    {
        $trip = $this->createManualTrip($this->user);
        $checkin = $this->createCheckinOnTrip($trip);

        $this->actAsApiUserWithAllScopes($this->user);
        $response = $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/{$checkin->originStopover->uuid}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('train_stopovers', ['id' => $checkin->origin_stopover_id]);
    }

    public function test_a_trip_cannot_be_reduced_below_two_stopovers(): void
    {
        $trip = $this->createManualTrip($this->user);
        $this->actAsApiUserWithAllScopes($this->user);

        // The factory creates four stopovers, so two of them may go
        $remaining = $trip->stopovers;
        $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/{$remaining[1]->uuid}")->assertNoContent();
        $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/{$remaining[2]->uuid}")->assertNoContent();

        $response = $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/{$remaining[0]->uuid}");
        $response->assertStatus(422);
        $this->assertDatabaseHas('train_stopovers', ['id' => $remaining[0]->id]);
    }

    public function test_stopover_of_another_trip_is_not_found(): void
    {
        $trip = $this->createManualTrip($this->user);
        $otherStopover = $this->firstStopover($this->createManualTrip($this->user));

        $this->actAsApiUserWithAllScopes($this->user);
        $this->deleteJson("/api/v1/trips/{$trip->uuid}/stopovers/{$otherStopover->uuid}")->assertNotFound();
    }

    public function test_checkins_are_recalculated_when_a_stopover_time_changes(): void
    {
        $trip = $this->createManualTrip($this->user);
        $checkin = $this->createCheckinOnTrip($trip);
        $newArrival = $checkin->destinationStopover->arrival_planned->clone()->addMinutes(30);
        $oldDuration = $checkin->fresh()->duration;

        $this->actAsApiUserWithAllScopes($this->user);
        $this->putJson("/api/v1/trips/{$trip->uuid}/stopovers/{$checkin->destinationStopover->uuid}", [
            'arrivalPlanned' => $newArrival->toIso8601String(),
            'departurePlanned' => $newArrival->toIso8601String(),
        ])->assertNoContent();

        $checkin->refresh();
        $this->assertTrue($newArrival->equalTo($checkin->arrival));
        $this->assertSame($oldDuration + 30, $checkin->duration);
    }

    private function createCheckinOnTrip(Trip $trip): Checkin
    {
        $checkin = Checkin::factory()->create([
            'user_id' => $this->user->id,
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $this->firstStopover($trip)->id,
            'destination_stopover_id' => $this->lastStopover($trip)->id,
            'departure' => $trip->departure,
            'arrival' => $trip->arrival,
        ]);

        // The factory pins real times onto the stopovers, which would shadow any planned time change
        Stopover::where('trip_id', $trip->trip_id)->update(['arrival_real' => null, 'departure_real' => null]);

        return $checkin->fresh();
    }
}
