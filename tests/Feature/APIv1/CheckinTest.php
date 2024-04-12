<?php

namespace Tests\Feature\APIv1;

use App\Models\Checkin;
use App\Models\Trip;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class CheckinTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testOauthClientIdIsSavedOnApiCheckins(): void {
        $user  = User::factory()->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $trip  = Trip::factory()->create();

        $response = $this->postJson(
            uri:     '/api/v1/trains/checkin',
            data:    [
                         'tripId'      => $trip->trip_id,
                         'lineName'    => $trip->linename,
                         'start'       => $trip->originStation->id,
                         'departure'   => $trip->departure,
                         'destination' => $trip->destinationStation->id,
                         'arrival'     => $trip->arrival,
                     ],
            headers: ['Authorization' => 'Bearer ' . $token->accessToken],
        );
        $this->assertEquals(1, $response->json('data.status.client.id'));
    }
}
