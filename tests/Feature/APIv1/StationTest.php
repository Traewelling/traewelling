<?php

namespace Tests\Feature\APIv1;

use App\Models\RouteSegment;
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
        $response->assertNoContent();
        $this->assertDatabaseMissing('train_stations', [
            'id' => $station->id,
        ]);
    }

    public function test_admin_cannot_delete_station_with_references(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $station = Station::factory()->create();
        StationIdentifier::factory()->create(['station_id' => $station->id]);
        User::factory()->create(['home_id' => $station->id]);

        $response = $this->deleteJson('/api/v1/stations/' . $station->id);

        $response->assertConflict();
        $response->assertJsonPath('data.identifiers', 1);
        $response->assertJsonPath('data.homeUsers', 1);
        $this->assertDatabaseHas('train_stations', ['id' => $station->id]);
    }

    public function test_admin_can_view_station_usages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $station = Station::factory()->create();
        $otherStation = Station::factory()->create();
        StationIdentifier::factory()->create(['station_id' => $station->id]);
        RouteSegment::factory()->create([
            'from_station_id' => $station->id,
            'to_station_id' => $otherStation->id,
        ]);
        User::factory()->create(['home_id' => $station->id]);

        $response = $this->getJson('/api/v1/stations/' . $station->id . '/usages');

        $response->assertOk();
        $response->assertJson(['data' => [
            'stopovers' => 0,
            'trips' => 0,
            'events' => 0,
            'eventSuggestions' => 0,
            'identifiers' => 1,
            'routeSegments' => 1,
            'homeUsers' => 1,
        ]]);
    }

    public function test_user_cannot_view_station_usages(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $station = Station::factory()->create();

        $this->getJson('/api/v1/stations/' . $station->id . '/usages')->assertForbidden();
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

    public function test_merge_station_updates_route_segments_and_home_stations(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $oldStation = Station::factory()->create();
        $newStation = Station::factory()->create();
        $otherStation = Station::factory()->create();

        $routeSegment = RouteSegment::factory()->create([
            'from_station_id' => $oldStation->id,
            'to_station_id' => $otherStation->id,
        ]);
        $homeUser = User::factory()->create(['home_id' => $oldStation->id]);

        $this->put('/api/v1/station/' . $oldStation->id . '/merge/' . $newStation->id)->assertOk();

        $this->assertDatabaseHas('route_segments', [
            'id' => $routeSegment->id,
            'from_station_id' => $newStation->id,
            'to_station_id' => $otherStation->id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $homeUser->id,
            'home_id' => $newStation->id,
        ]);
    }

    public function test_admin_can_update_station(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Passport::actingAs($admin, ['*']);

        $station = Station::factory()->create(['name' => 'Old Name', 'latitude' => 48.0, 'longitude' => 8.0]);

        $this->patchJson('/api/v1/stations/' . $station->id, [
            'name' => 'New Name',
            'latitude' => 49.123456,
            'longitude' => 9.654321,
        ])->assertOk();

        $this->assertDatabaseHas('train_stations', [
            'id' => $station->id,
            'name' => 'New Name',
            'latitude' => 49.123456,
            'longitude' => 9.654321,
        ]);
    }

    public function test_user_cannot_update_station(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $station = Station::factory()->create(['name' => 'Old Name']);

        $this->patchJson('/api/v1/stations/' . $station->id, ['name' => 'New Name'])
            ->assertForbidden();

        $this->assertDatabaseHas('train_stations', ['id' => $station->id, 'name' => 'Old Name']);
    }
}
