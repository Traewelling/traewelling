<?php

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\ApiTestCase;

class StatusTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testActiveStatusesWithoutAnyStatus(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->get(
            uri:     '/api/v1/user/statuses/active',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertNotFound();
        $this->assertEquals('User doesn\'t have any checkins', $response->json('message'));
    }

    public function testActiveStatusesWithActiveStatus(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $departure = Date::now()->subHour();
        $arrival   = Date::now()->addHour();

        $status  = Status::factory([
                                       'user_id' => $user->id,
                                   ])->create();
        $checkin = TrainCheckin::factory([
                                             'status_id' => $status->id,
                                             'user_id'   => $user->id,
                                             'departure' => $departure,
                                             'arrival'   => $arrival,
                                         ])->create();
        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $checkin->Origin->id,
                                   'arrival_planned'   => $departure,
                                   'arrival_real'      => $departure,
                                   'departure_planned' => $departure,
                                   'departure_real'    => $departure,
                               ])->create();
        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $checkin->Destination->id,
                                   'arrival_planned'   => $arrival,
                                   'arrival_real'      => $arrival,
                                   'departure_planned' => $arrival,
                                   'departure_real'    => $arrival,
                               ])->create();

        $response = $this->get(
            uri:     '/api/v1/user/statuses/active',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'id',
                                               'body',
                                               'username',
                                               'profilePicture',
                                               'train' => [
                                                   'trip',
                                                   'hafasId',
                                                   'category',
                                                   'number',
                                                   'lineName',
                                                   'origin'      => [
                                                       'id',
                                                   ],
                                                   'destination' => [
                                                       'id',
                                                   ]

                                               ],
                                               //and more...
                                           ]
                                       ]);

        $this->assertEquals($checkin->originStation->id, $response->json('data.train.origin.id'));
        $this->assertEquals($checkin->destinationStation->id, $response->json('data.train.destination.id'));
    }

    public function testActiveStatusesWithInactiveStatus(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $departure = Date::now()->addHour();
        $arrival   = Date::now()->addHours(2);

        $status  = Status::factory([
                                       'user_id' => $user->id,
                                   ])->create();
        $checkin = TrainCheckin::factory([
                                             'status_id' => $status->id,
                                             'user_id'   => $user->id,
                                             'departure' => $departure,
                                             'arrival'   => $arrival,
                                         ])->create();
        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $checkin->Origin->id,
                                   'arrival_planned'   => $departure,
                                   'arrival_real'      => $departure,
                                   'departure_planned' => $departure,
                                   'departure_real'    => $departure,
                               ])->create();
        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $checkin->Destination->id,
                                   'arrival_planned'   => $arrival,
                                   'arrival_real'      => $arrival,
                                   'departure_planned' => $arrival,
                                   'departure_real'    => $arrival,
                               ])->create();

        $response = $this->get(
            uri:     '/api/v1/user/statuses/active',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertNotFound();
        $this->assertEquals('No active status', $response->json('message'));
    }

    public function testStatusUpdate(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $status = Status::factory([
                                      'user_id'    => $user->id,
                                      'body'       => 'old body',
                                      'visibility' => StatusVisibility::PRIVATE->value,
                                      'business'   => Business::PRIVATE->value,
                                  ])->create();
        TrainCheckin::factory([
                                  'status_id' => $status->id,
                                  'user_id'   => $user->id,
                              ])->create();

        $this->assertEquals('old body', $status->body);
        $this->assertEquals(StatusVisibility::PRIVATE->value, $status->visibility->value);
        $this->assertEquals(Business::PRIVATE->value, $status->business->value);

        $response = $this->put(
            uri:     '/api/v1/statuses/' . $status->id,
            data:    [
                         'body'       => 'new body',
                         'visibility' => StatusVisibility::PUBLIC->value,
                         'business'   => Business::BUSINESS->value,
                     ],
            headers: ['Authorization' => 'Bearer ' . $userToken],
        );
        $response->assertOk();

        $status->refresh();
        $this->assertEquals('new body', $status->body);
        $this->assertEquals(StatusVisibility::PUBLIC->value, $status->visibility->value);
        $this->assertEquals(Business::BUSINESS->value, $status->business->value);
    }


    public function testStatusUpdateWithChangedDestination() {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $firstDeparture = Date::now()->addHour();
        $secondArrival  = Date::now()->addHours(2);
        $thirdArrival   = Date::now()->addHours(3);

        $firstStation  = TrainStation::factory()->create();
        $secondStation = TrainStation::factory()->create();
        $thirdStation  = TrainStation::factory()->create();

        $status  = Status::factory([
                                       'user_id'    => $user->id,
                                       'visibility' => StatusVisibility::PRIVATE->value,
                                       'business'   => Business::PRIVATE->value,
                                   ])->create();
        $checkin = TrainCheckin::factory([
                                             'status_id'   => $status->id,
                                             'user_id'     => $user->id,
                                             'origin'      => $firstStation->ibnr,
                                             'departure'   => $firstDeparture->toDateTimeString(),
                                             'destination' => $secondStation->ibnr,
                                             'arrival'     => $secondArrival->toDateTimeString(),
                                         ])->create();

        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $firstStation->id,
                                   'arrival_planned'   => $firstDeparture,
                                   'arrival_real'      => $firstDeparture,
                                   'departure_planned' => $firstDeparture,
                                   'departure_real'    => $firstDeparture,
                               ])->create();
        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $secondStation->id,
                                   'arrival_planned'   => $secondArrival,
                                   'arrival_real'      => $secondArrival,
                                   'departure_planned' => $secondArrival,
                                   'departure_real'    => $secondArrival,
                               ])->create();
        TrainStopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $thirdStation->id,
                                   'arrival_planned'   => $thirdArrival,
                                   'arrival_real'      => $thirdArrival,
                                   'departure_planned' => $thirdArrival,
                                   'departure_real'    => $thirdArrival,
                               ])->create();

        $this->assertEquals($checkin->originStation->id, $firstStation->id);
        $this->assertEquals($checkin->destinationStation->id, $secondStation->id);

        $response = $this->put(
            uri:     '/api/v1/statuses/' . $status->id,
            data:    [
                         'visibility'                => StatusVisibility::PUBLIC->value,
                         'business'                  => Business::BUSINESS->value,
                         'destinationId'             => $thirdStation->id,
                         'destinationArrivalPlanned' => $thirdArrival->toDateTimeString(),
                     ],
            headers: ['Authorization' => 'Bearer ' . $userToken],
        );
        $response->assertOk();

        $checkin = $checkin->fresh();

        $this->assertEquals($checkin->originStation->id, $firstStation->id);
        $this->assertEquals($checkin->destinationStation->id, $thirdStation->id);
    }
}
