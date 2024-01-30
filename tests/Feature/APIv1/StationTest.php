<?php

namespace Tests\Feature\APIv1;

use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StationTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCantAccessStationListBackend(): void {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/admin/stations');
        $response->assertForbidden();
    }

    public function testUserCannotCreateStation(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $response = $this->post('/api/v1/station', [
            'ibnr'          => 123456,
            'rilIdentifier' => 'test',
            'name'          => 'Test Station',
            'latitude'      => 12.345678,
            'longitude'     => 12.345678,
        ]);
        $response->assertForbidden();
    }

    public function testAdminCanCreateStation(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);
        $response = $this->post('/api/v1/station', [
            'ibnr'          => 123456,
            'rilIdentifier' => 'test',
            'name'          => 'Test Station',
            'latitude'      => 12.345678,
            'longitude'     => 12.345678,
        ]);
        $response->assertCreated();
        $this->assertDatabaseHas('train_stations', [
            'ibnr'          => 123456,
            'rilIdentifier' => 'test',
            'name'          => 'Test Station',
            'latitude'      => 12.345678,
            'longitude'     => 12.345678,
        ]);
    }

    public function testUserCantDeleteStation(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $station  = Station::factory()->create();
        $response = $this->delete('/api/v1/station/' . $station->id);
        $response->assertForbidden();
        $this->assertDatabaseHas('train_stations', [
            'id' => $station->id,
        ]);
    }

    public function testAdminCanDeleteStation(): void {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Passport::actingAs($user, ['*']);
        $station  = Station::factory()->create();
        $response = $this->delete('/api/v1/station/' . $station->id);
        $response->assertOk();
        $this->assertDatabaseMissing('train_stations', [
            'id' => $station->id,
        ]);
    }

    public function testUserCannotMergeStation(): void {
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

    public function testAdminCanMergeStation(): void {
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
}
