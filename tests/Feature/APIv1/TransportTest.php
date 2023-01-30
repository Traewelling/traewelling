<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;

class TransportTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testGetDeparturesFetchTripAndCheckin(): void {
        //Test departures
        $station   = 'Hannover Hbf';
        $timestamp = Date::parse('next monday 8 am');
        $response  = $this->get(
            uri:     '/api/v1/trains/station/' . $station . '/departures?when=' . urlencode($timestamp->toIso8601String()),
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               '*' => [
                                                   'tripId',
                                                   'stop',
                                                   'when',
                                                   'plannedWhen',
                                                   //and more...
                                               ]
                                           ],
                                           'meta' => [
                                               'station' => [
                                                   'id',
                                                   'ibnr',
                                                   'name',
                                                   'latitude',
                                                   'longitude',
                                                   'rilIdentifier'
                                               ],
                                               'times'   => [
                                                   'now',
                                                   'prev',
                                                   'next',
                                               ]
                                           ]
                                       ]);

        $this->assertEquals('Hannover Hbf', $response->json('meta.station.name'));
        $this->assertGreaterThan(0, $response->json('data'));

        $departure = $response->json('data')[0];

        //Fetch trip with wrong origin / stopover
        $response = $this->get(
            uri:     '/api/v1/trains/trip'
                     . '?hafasTripId=' . $departure['tripId']
                     . '&lineName=' . $departure['line']['name']
                     . '&start=' . ($departure['stop']['id'] + 99999),
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertStatus(400);
        // Fetch correct trip
        $response = $this->get(
            uri:     '/api/v1/trains/trip'
                     . '?tripId=' . $departure['tripId']
                     . '&lineName=' . $departure['line']['name']
                     . '&start=' . $departure['stop']['id'],
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'id',
                                               'category',
                                               'number',
                                               'lineName',
                                               'origin'      => [
                                                   'id', //and more...
                                               ],
                                               'destination' => [
                                                   'id', //and more...
                                               ],
                                               'stopovers'   => [
                                                   '*' => [
                                                       'id',
                                                       'name',
                                                       'arrivalPlanned',
                                                       'arrivalReal',
                                                       'departurePlanned',
                                                       'departureReal',
                                                   ]
                                               ]
                                           ]
                                       ]);
        $trip = $response->json('data');

        //Now checkin...
        $response = $this->postJson(
            uri:     '/api/v1/trains/checkin',
            data:    [
                         'tripId'      => $departure['tripId'],
                         'lineName'    => $departure['line']['name'],
                         'start'       => $trip['stopovers'][0]['evaIdentifier'],
                         'departure'   => $trip['stopovers'][0]['departurePlanned'],
                         'destination' => $trip['stopovers'][1]['evaIdentifier'],
                         'arrival'     => $trip['stopovers'][1]['arrivalPlanned'],
                         'ibnr'        => true,
                     ],
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertCreated();
        $response->assertJsonStructure([
                                           'data' => [
                                               'status' => [
                                                   'id', 'body', 'type', 'user', //and more...
                                               ],
                                               'points' => [
                                                   'points', //TODO: should be renamed... this sounds weird duplictated.
                                                   'calculation' => [
                                                       'base', 'distance', 'factor', 'reason',
                                                   ],
                                                   'additional'
                                               ],
                                               'alsoOnThisConnection',
                                           ]
                                       ]);

        //Do the same thing again! Should be a CheckInCollision
        $response = $this->postJson(
            uri:     '/api/v1/trains/checkin',
            data:    [
                         'tripId'      => $departure['tripId'],
                         'lineName'    => $departure['line']['name'],
                         'start'       => $trip['stopovers'][0]['evaIdentifier'],
                         'departure'   => $trip['stopovers'][0]['departurePlanned'],
                         'destination' => $trip['stopovers'][1]['evaIdentifier'],
                         'arrival'     => $trip['stopovers'][1]['arrivalPlanned'],
                         'ibnr'        => true,
                     ],
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertStatus(400);
    }

    public function testGetStationByCoordinates(): void {
        $response = $this->get(
            uri:     '/api/v1/trains/station/nearby?latitude=52.376564&longitude=9.741046&limit=1',
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'id',
                                               'name',
                                               'latitude',
                                               'longitude',
                                               'ibnr',
                                               'rilIdentifier',
                                           ]
                                       ]);
        $this->assertEquals('Hannover Hbf', $response->json('data.name'));
    }

    public function testGetStationByCoordinatesIfNoStationIsNearby(): void {
        $response = $this->get(
            uri:     '/api/v1/trains/station/nearby?latitude=0&longitude=0&limit=1',
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertNotFound();
    }

    public function testSetHome(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $this->assertNull($user->home);

        $response = $this->put(
            uri:     '/api/v1/trains/station/Hannover Hbf/home',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $user->refresh();
        $this->assertEquals('Hannover Hbf', $user->home?->name);
    }

    public function testAutocompleteWithDs100(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->get(
            uri:     '/api/v1/trains/station/autocomplete/HH',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               '*' => [
                                                   'ibnr',
                                                   'rilIdentifier',
                                                   'name',
                                               ]
                                           ]
                                       ]);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('HH', $response->json('data.0.rilIdentifier'));
        $this->assertEquals('Hannover Hbf', $response->json('data.0.name'));
    }
}
