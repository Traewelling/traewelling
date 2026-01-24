<?php

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class TrainStationTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_history(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        // Check if endpoint is working without data
        $response = $this->get('/api/v1/trains/station/history');
        $response->assertJsonStructure(['data' => []]);
        $response->assertJsonCount(0, 'data');

        // Create random checkin
        Checkin::factory(['user_id' => $user->id])->create();

        // Check if endpoint is working with data
        $response = $this->get('/api/v1/trains/station/history');
        $response->assertJsonStructure(['data' => [
            '*' => [
                'id',
                'name',
                'latitude',
                'longitude',
                'ibnr',
                'rilIdentifier',
            ],
        ]]);
        $response->assertJsonCount(1, 'data');

        $station = Station::where('id', $response->json('data.0.id'))->first();
        $this->assertNotNull($station);
        $this->assertEquals($station->name, $response->json('data.0.name'));
        $this->assertEquals($station->getIdentifier(\App\StationIdentifierType::DE_DB_IBNR), $response->json('data.0.ibnr'));
        $this->assertEquals($station->getIdentifier(\App\StationIdentifierType::DE_DB_RIL100), $response->json('data.0.rilIdentifier'));
        $this->assertEquals($station->latitude, $response->json('data.0.latitude'));
        $this->assertEquals($station->longitude, $response->json('data.0.longitude'));
    }
}
