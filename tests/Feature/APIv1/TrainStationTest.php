<?php

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class TrainStationTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testHistory(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);

        //Check if endpoint is working without data
        $response = $this->get('/api/v1/trains/station/history');
        $response->assertJsonStructure(['data' => []]);
        $response->assertJsonCount(0, 'data');

        //Create random checkin
        Checkin::factory(['user_id' => $user->id])->create();

        //Check if endpoint is working with data
        $response = $this->get('/api/v1/trains/station/history');
        $response->assertJsonStructure(['data' => [
            '*' => [
                'id',
                'name',
                'latitude',
                'longitude',
                'ibnr',
                'rilIdentifier',
            ]
        ]]);
        $response->assertJsonCount(1, 'data');
        $this->assertNotNull($response->json('data.0.rilIdentifier'));
        $this->assertNotEquals(0, $response->json('data.0.latitude'));
        $this->assertNotEquals(0, $response->json('data.0.longitude'));
    }
}
