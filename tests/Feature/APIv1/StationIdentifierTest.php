<?php

namespace Tests\Feature\APIv1;

use App\Enum\StationIdentifierType;
use App\Models\Station;
use App\Models\StationIdentifier;
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
        )->assertNoContent();

        $this->assertDatabaseHas('station_identifiers', [
            'id' => $identifier->id,
            'station_id' => $target->id,
        ]);
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
