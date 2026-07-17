<?php

namespace Tests\Feature\APIv1;

use App\Enum\HafasTravelType;
use App\Enum\StationIdentifierType;
use App\Enum\TripSource;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StationIdentifierTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_identifier_to_station(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $station = Station::factory()->create();

        $response = $this->postJson("/api/v1/stations/{$station->id}/identifiers", [
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ]);

        $response->assertNoContent(201);
        $this->assertDatabaseHas('station_identifiers', [
            'station_id' => $station->id,
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
            'origin' => null,
        ]);
    }

    public function test_user_cannot_add_identifier_to_station(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $station = Station::factory()->create();

        $this->postJson("/api/v1/stations/{$station->id}/identifiers", [
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ])->assertForbidden();
    }

    public function test_admin_can_update_identifier(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $station = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create([
            'station_id' => $station->id,
            'type' => StationIdentifierType::MOTIS->value,
            'identifier' => 'old-value',
        ]);

        $response = $this->patchJson("/api/v1/stations/{$station->id}/identifiers/{$identifier->id}", [
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('station_identifiers', [
            'id' => $identifier->id,
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ]);
    }

    public function test_user_cannot_update_identifier(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $station = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create(['station_id' => $station->id]);

        $this->patchJson("/api/v1/stations/{$station->id}/identifiers/{$identifier->id}", [
            'type' => StationIdentifierType::DE_DB_IBNR->value,
            'identifier' => '8000191',
        ])->assertForbidden();
    }

    public function test_admin_can_move_identifier_to_another_station(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $source = Station::factory()->create();
        $target = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create(['station_id' => $source->id]);

        $this->putJson(
            "/api/v1/stations/{$source->id}/identifiers/{$identifier->id}/move",
            ['target_station_id' => $target->id],
        )->assertOk();

        $this->assertDatabaseHas('station_identifiers', [
            'id' => $identifier->id,
            'station_id' => $target->id,
        ]);
    }

    public function test_cannot_move_identifier_to_same_station(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $source = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create(['station_id' => $source->id]);

        $this->putJson(
            "/api/v1/stations/{$source->id}/identifiers/{$identifier->id}/move",
            ['target_station_id' => $source->id],
        )->assertUnprocessable();
    }

    /**
     * @return array{0: Station, 1: Station, 2: StationIdentifier, 3: Trip, 4: Stopover}
     */
    private function createTripWithIdentifierStopover(): array
    {
        $source = Station::factory()->create();
        $target = Station::factory()->create();
        $destination = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create(['station_id' => $source->id]);

        $trip = Trip::create([
            'trip_id' => 'move-test-trip',
            'category' => HafasTravelType::REGIONAL,
            'number' => 'RB 1',
            'linename' => 'RB 1',
            'origin_id' => $source->id,
            'destination_id' => $destination->id,
            'departure' => '2026-07-17T10:00:00Z',
            'arrival' => '2026-07-17T11:00:00Z',
            'source' => TripSource::TRANSITOUS,
        ]);

        $originStopover = Stopover::factory()->create([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $source->id,
            'station_identifier_id' => $identifier->id,
            'arrival_planned' => '2026-07-17T10:00:00Z',
            'departure_planned' => '2026-07-17T10:00:00Z',
        ]);
        Stopover::factory()->create([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $destination->id,
            'arrival_planned' => '2026-07-17T11:00:00Z',
            'departure_planned' => '2026-07-17T11:00:00Z',
        ]);

        return [$source, $target, $identifier, $trip, $originStopover];
    }

    public function test_move_identifier_also_moves_stopovers_trips_and_route_segments(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        [$source, $target, $identifier, $trip, $originStopover] = $this->createTripWithIdentifierStopover();
        $routeSegment = RouteSegment::factory()->create([
            'from_station_id' => $source->id,
            'from_identifier_id' => $identifier->id,
        ]);

        $response = $this->putJson(
            "/api/v1/stations/{$source->id}/identifiers/{$identifier->id}/move",
            ['target_station_id' => $target->id],
        );

        $response->assertOk();
        $response->assertJson(['data' => [
            'movedStopovers' => 1,
            'skippedStopovers' => 0,
            'updatedTrips' => 1,
            'updatedRouteSegments' => 1,
        ]]);

        $this->assertDatabaseHas('train_stopovers', ['id' => $originStopover->id, 'train_station_id' => $target->id]);
        $this->assertDatabaseHas('hafas_trips', ['trip_id' => $trip->trip_id, 'origin_id' => $target->id, 'destination_id' => $trip->destination_id]);
        $this->assertDatabaseHas('route_segments', ['id' => $routeSegment->id, 'from_station_id' => $target->id]);
    }

    public function test_move_identifier_skips_stopovers_conflicting_with_existing_duplicates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        [$source, $target, $identifier, $trip, $originStopover] = $this->createTripWithIdentifierStopover();

        // duplicate created by the former refresh bug: same trip and planned times, already on the target station
        Stopover::factory()->create([
            'trip_id' => $trip->trip_id,
            'train_station_id' => $target->id,
            'arrival_planned' => '2026-07-17T10:00:00Z',
            'departure_planned' => '2026-07-17T10:00:00Z',
        ]);

        $response = $this->putJson(
            "/api/v1/stations/{$source->id}/identifiers/{$identifier->id}/move",
            ['target_station_id' => $target->id],
        );

        $response->assertOk();
        $response->assertJson(['data' => [
            'movedStopovers' => 0,
            'skippedStopovers' => 1,
            'updatedTrips' => 0,
        ]]);

        $this->assertDatabaseHas('train_stopovers', ['id' => $originStopover->id, 'train_station_id' => $source->id]);
        $this->assertDatabaseHas('station_identifiers', ['id' => $identifier->id, 'station_id' => $target->id]);
    }

    public function test_user_cannot_move_identifier(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $source = Station::factory()->create();
        $target = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create(['station_id' => $source->id]);

        $this->putJson(
            "/api/v1/stations/{$source->id}/identifiers/{$identifier->id}/move",
            ['target_station_id' => $target->id],
        )->assertForbidden();
    }
}
