<?php

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Models\HafasTrip;
use App\Models\Status;
use App\Models\Checkin;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\Passport;
use Mockery\Generator\StringManipulation\Pass\Pass;
use Tests\ApiTestCase;

class StatusTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testActiveStatusesWithoutAnyStatus(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/user/statuses/active');
        $response->assertNotFound();
        $this->assertEquals('User doesn\'t have any checkins', $response->json('message'));
    }

    public function testActiveStatusesShowStatusesCurrentlyUnderway(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $departure = Date::now()->subHour();
        $arrival   = Date::now()->addHour();

        $checkin = Checkin::factory([
                                             'user_id'   => $user->id,
                                             'departure' => $departure,
                                             'arrival'   => $arrival,
                                         ])->create();

        $response = $this->get('/api/v1/user/statuses/active');
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

    public function testActiveStatusesDontShowStatusesFromTheFuture(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $departure = Date::now()->addHour();
        $arrival   = Date::now()->addHours(2);
        $trip = HafasTrip::factory(['departure' => $departure, 'arrival' => $arrival])->create();

        Checkin::factory([
                                  'user_id'     => $user->id,
                                  'departure'   => $trip->departure,
                                  'arrival'     => $trip->arrival,
                                  'origin'      => $trip->originStation->ibnr,
                                  'destination' => $trip->destinationStation->ibnr,
                                  'trip_id'     => $trip->trip_id,
                              ])->create();

        $response = $this->get('/api/v1/user/statuses/active');
        $response->assertNotFound();
        $this->assertEquals('No active status', $response->json('message'));
    }

    public function testStatusUpdate(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $status = Status::factory([
                                      'user_id'    => $user->id,
                                      'body'       => 'old body',
                                      'visibility' => StatusVisibility::PRIVATE->value,
                                      'business'   => Business::PRIVATE->value,
                                  ])->create();
        Checkin::factory([
                                  'status_id' => $status->id,
                                  'user_id'   => $user->id,
                              ])->create();

        $this->assertEquals('old body', $status->body);
        $this->assertEquals(StatusVisibility::PRIVATE->value, $status->visibility->value);
        $this->assertEquals(Business::PRIVATE->value, $status->business->value);

        $response = $this->put(
            uri:     '/api/v1/status/' . $status->id,
            data:    [
                         'body'       => 'new body',
                         'visibility' => StatusVisibility::PUBLIC->value,
                         'business'   => Business::BUSINESS->value,
                     ],
        );
        $response->assertOk();

        $status->refresh();
        $this->assertEquals('new body', $status->body);
        $this->assertEquals(StatusVisibility::PUBLIC->value, $status->visibility->value);
        $this->assertEquals(Business::BUSINESS->value, $status->business->value);
    }


    public function testStatusUpdateWithChangedDestination(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $checkin = Checkin::factory(['user_id' => $user->id])->create();

        //Create a new stopover now (factory creates departure 1 hour ago and arrival in 1 hour)
        $newStation     = Station::factory()->create();
        $thirdTimestamp = Date::now()->setSecond(0);
        Stopover::factory([
                                   'trip_id'           => $checkin->trip_id,
                                   'train_station_id'  => $newStation->id,
                                   'arrival_planned'   => $thirdTimestamp,
                                   'arrival_real'      => $thirdTimestamp,
                                   'departure_planned' => $thirdTimestamp,
                                   'departure_real'    => $thirdTimestamp,
                               ])->create();

        $this->assertNotEquals($checkin->originStation->id, $newStation->id);
        $this->assertNotEquals($checkin->destinationStation->id, $newStation->id);

        $response = $this->put(
            uri:     '/api/v1/status/' . $checkin->status_id,
            data:    [
                         'visibility'                => StatusVisibility::PUBLIC->value,
                         'business'                  => Business::BUSINESS->value,
                         'destinationId'             => $newStation->id,
                         'destinationArrivalPlanned' => $thirdTimestamp->toDateTimeString(),
                     ],
        );
        $response->assertOk();

        $checkin = $checkin->fresh();

        $this->assertEquals($checkin->destinationStation->id, $newStation->id);
    }
}
