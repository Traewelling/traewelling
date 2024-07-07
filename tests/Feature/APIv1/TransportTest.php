<?php

namespace Feature\APIv1;

use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class TransportTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testGetDeparturesFetchTripAndCheckin(): void {
        Http::fake([
                       '/locations*'                              => Http::response([self::FRANKFURT_HBF]),
                       '/stops/8000105/departures*'               => Http::response([self::ICE802]),
                       '/trips/' . urlencode(self::TRIP_ID) . '*' => Http::response(self::TRIP_INFO),
                   ]);

        //Test departures
        $station   = Station::factory(['ibnr' => self::FRANKFURT_HBF['id'], 'name' => self::FRANKFURT_HBF['name']])->create();
        $timestamp = Date::parse('next monday 8 am');
        $this->actAsApiUserWithAllScopes();
        $response = $this->get(
            uri: '/api/v1/station/' . $station->id . '/departures?when=' . urlencode($timestamp->toIso8601String()),
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

        $this->assertEquals($station->name, $response->json('meta.station.name'));
        $this->assertGreaterThan(0, $response->json('data'));

        $departure = $response->json('data')[0];

        //Fetch trip with wrong origin / stopover
        $response = $this->get(
            uri: '/api/v1/trains/trip'
                 . '?hafasTripId=' . $departure['tripId']
                 . '&lineName=' . $departure['line']['name']
                 . '&start=' . ($departure['stop']['id'] + 99999),
        );
        $response->assertStatus(400);
        // Fetch correct trip
        $response = $this->get(
            uri: '/api/v1/trains/trip'
                 . '?hafasTripId=' . $departure['tripId']
                 . '&lineName=' . $departure['line']['name']
                 . '&start=' . $departure['stop']['id'],
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
            uri:  '/api/v1/trains/checkin',
            data: [
                      'tripId'      => $departure['tripId'],
                      'lineName'    => $departure['line']['name'],
                      'start'       => $trip['stopovers'][0]['evaIdentifier'],
                      'departure'   => $trip['stopovers'][0]['departurePlanned'],
                      'destination' => $trip['stopovers'][1]['evaIdentifier'],
                      'arrival'     => $trip['stopovers'][1]['arrivalPlanned'],
                      'ibnr'        => true,
                  ],
        );
        $response->assertCreated();
        $response->assertJsonStructure([
                                           'data' => [
                                               'status' => [
                                                   'id', 'body', 'user', //and more...
                                               ],
                                               'points' => [
                                                   'points', //TODO: should be renamed... this sounds weird duplicated.
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
            uri:  '/api/v1/trains/checkin',
            data: [
                      'tripId'      => $departure['tripId'],
                      'lineName'    => $departure['line']['name'],
                      'start'       => $trip['stopovers'][0]['evaIdentifier'],
                      'departure'   => $trip['stopovers'][0]['departurePlanned'],
                      'destination' => $trip['stopovers'][1]['evaIdentifier'],
                      'arrival'     => $trip['stopovers'][1]['arrivalPlanned'],
                      'ibnr'        => true,
                  ],
        );
        $response->assertStatus(409);
    }

    public function testGetStationByCoordinates(): void {
        Http::fake(["*/stops/nearby*" => Http::response([array_merge(
                                                             self::HANNOVER_HBF,
                                                             ["distance" => 421]
                                                         )])]);

        $this->actAsApiUserWithAllScopes();
        $response = $this->get('/api/v1/trains/station/nearby?latitude=52.376564&longitude=9.741046&limit=1');
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
        Http::fake(["*/stops/nearby*" => Http::response([])]);

        $this->actAsApiUserWithAllScopes();
        $response = $this->get('/api/v1/trains/station/nearby?latitude=0&longitude=0&limit=1');
        $response->assertNotFound();
    }

    public function testSetHome(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $station = Station::factory()->create();

        $this->assertNull($user->home);

        $response = $this->put('/api/v1/station/' . $station->id . '/home');
        $response->assertOk();
        $user->refresh();
        $this->assertEquals($station->name, $user->home?->name);
    }

    public function testAutocompleteWithDs100(): void {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        Http::fake(["*/stations/" . self::HANNOVER_HBF['ril100'] => Http::response(self::HANNOVER_HBF)]);

        $response = $this->get('/api/v1/trains/station/autocomplete/HH');
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
