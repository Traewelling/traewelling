<?php

namespace Tests\Feature\APIv1;

use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StationTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_cant_access_station_list_backend(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin/stations');
        $response->assertForbidden();
    }

    public function test_admin_can_access_station_list_backend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);
        $response = $this->get('/admin/stations?query=Karlsruhe');
        $response->assertOk();
    }

    public function test_user_cant_access_station_view_backend(): void
    {
        $user = User::factory()->create();
        $station = Station::factory()->create();
        $response = $this->actingAs($user)->get('/admin/stations/' . $station->id);
        $response->assertForbidden();
    }

    public function test_admin_can_access_station_view_backend(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);
        $station = Station::factory()->create();
        $response = $this->get('/admin/stations/' . $station->id);
        $response->assertOk();
    }

    public function test_user_cannot_create_station(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $response = $this->post('/api/v1/station', [
            'name' => 'Test Station',
            'latitude' => 12.345678,
            'longitude' => 12.345678,
        ]);
        $response->assertForbidden();
    }

    public function test_admin_can_create_station(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);
        $response = $this->post('/api/v1/station', [
            'name' => 'Test Station',
            'latitude' => 12.345678,
            'longitude' => 12.345678,
        ]);
        $response->assertCreated();
        $this->assertDatabaseHas('train_stations', [
            'name' => 'Test Station',
            'latitude' => 12.345678,
            'longitude' => 12.345678,
        ]);
    }

    public function test_user_cant_delete_station(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $station = Station::factory()->create();
        $response = $this->delete('/api/v1/station/' . $station->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('train_stations', [
            'id' => $station->id,
        ]);
    }

    public function test_admin_can_delete_station(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);
        $station = Station::factory()->create();
        $response = $this->delete('/api/v1/station/' . $station->id);
        $response->assertOk();
        $this->assertDatabaseMissing('train_stations', [
            'id' => $station->id,
        ]);
    }

    public function test_user_cannot_merge_station(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $oldStation = Station::factory()->create();
        $newStation = Station::factory()->create();

        $response = $this->put('/api/v1/station/' . $oldStation->id . '/merge/' . $newStation->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('train_stations', [
            'id' => $oldStation->id,
        ]);
    }

    public function test_admin_can_merge_station(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);

        $oldStation = Station::factory()->create();
        $newStation = Station::factory()->create();

        $response = $this->put('/api/v1/station/' . $oldStation->id . '/merge/' . $newStation->id);
        $response->assertOk();
        $this->assertDatabaseMissing('train_stations', [
            'id' => $oldStation->id,
        ]);
    }

    public function test_admin_can_move_identifier_to_another_station(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $source = Station::factory()->create();
        $target = Station::factory()->create();
        $identifier = StationIdentifier::factory()->create(['station_id' => $source->id]);

        $response = $this->putJson(
            "/api/v1/stations/{$source->id}/identifiers/{$identifier->id}/move",
            ['target_station_id' => $target->id],
        );

        $response->assertNoContent();
        $this->assertDatabaseHas('station_identifiers', [
            'id' => $identifier->id,
            'station_id' => $target->id,
        ]);
    }
}
