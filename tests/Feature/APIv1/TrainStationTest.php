<?php

namespace Tests\Feature\APIv1;

use App\Models\TrainCheckin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class TrainStationTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testHistory(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        //Check if endpoint is working without data
        $response = $this->get(
            uri:     '/api/v1/trains/station/history',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertJsonStructure(['data' => []]);
        $response->assertJsonCount(0, 'data');

        //Create random checkin
        TrainCheckin::factory(['user_id' => $user->id])->create();

        //Check if endpoint is working with data
        $response = $this->get(
            uri:     '/api/v1/trains/station/history',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
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
