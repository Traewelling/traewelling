<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\TrainStation;
use App\Models\TrainStopover;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Tests\ApiTestCase;

class StatusTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testActiveStatusesWithoutAnyStatus(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token')->accessToken;

        $response = $this->get(
            uri:     '/api/v1/user/statuses/active',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertNotFound();
        $this->assertEquals('User doesn\'t have any checkins', $response->json('message'));
    }

    public function testActiveStatusesWithActiveStatus(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token')->accessToken;

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
        $userToken = $user->createToken('token')->accessToken;

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
}
