<?php

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\Station;
use App\Models\Status;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class StatusTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_active_statuses_without_any_status(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/user/statuses/active');
        $response->assertNoContent();
    }

    public function test_active_statuses_show_statuses_currently_underway(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $departure = Date::now()->subHour();
        $arrival = Date::now()->addHour();

        $checkin = Checkin::factory([
            'user_id' => $user->id,
            'departure' => $departure,
            'arrival' => $arrival,
        ])->create();

        $response = $this->get('/api/v1/user/statuses/active');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'body',
                'userDetails' => [
                    'id',
                    'displayName',
                    'username',
                    'profilePicture',
                    'preventIndex',
                ],
                'train' => [
                    'trip',
                    'hafasId',
                    'category',
                    'number',
                    'lineName',
                    'origin' => [
                        'id',
                    ],
                    'destination' => [
                        'id',
                    ],

                ],
                // and more...
            ],
        ]);

        $this->assertEquals($checkin->originStopover->station->id, $response->json('data.train.origin.id'));
        $this->assertEquals($checkin->destinationStopover->station->id, $response->json('data.train.destination.id'));
    }

    public function test_active_statuses_dont_show_statuses_from_the_future(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $departure = Date::now()->addHour();
        $arrival = Date::now()->addHours(2);
        $trip = Trip::factory(['departure' => $departure, 'arrival' => $arrival])->create();

        Checkin::factory([
            'user_id' => $user->id,
            'departure' => $trip->departure,
            'arrival' => $trip->arrival,
            'trip_id' => $trip->trip_id,
        ])->create();

        $response = $this->get('/api/v1/user/statuses/active');
        $response->assertNoContent();
    }

    public function test_status_update(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $status = Status::factory([
            'user_id' => $user->id,
            'body' => 'old body',
            'visibility' => StatusVisibility::PRIVATE->value,
            'business' => Business::PRIVATE->value,
        ])->create();
        Checkin::factory([
            'status_id' => $status->id,
            'user_id' => $user->id,
        ])->create();

        $this->assertEquals('old body', $status->body);
        $this->assertEquals(StatusVisibility::PRIVATE->value, $status->visibility->value);
        $this->assertEquals(Business::PRIVATE->value, $status->business->value);

        $response = $this->put(
            uri: '/api/v1/status/' . $status->id,
            data: [
                'body' => 'new body',
                'visibility' => StatusVisibility::PUBLIC->value,
                'business' => Business::BUSINESS->value,
                'eventId' => Event::factory()->create()->id,
            ],
        );
        $response->assertOk();

        $status->refresh();
        $this->assertEquals('new body', $status->body);
        $this->assertEquals(StatusVisibility::PUBLIC->value, $status->visibility->value);
        $this->assertEquals(Business::BUSINESS->value, $status->business->value);
        $this->assertEquals(1, $status->event_id);

        // Also check, if body & eventId can be set to null
        $response = $this->put(
            uri: '/api/v1/status/' . $status->id,
            data: [
                'body' => null,
                'visibility' => StatusVisibility::PUBLIC->value,
                'business' => Business::BUSINESS->value,
                'eventId' => null,
            ],
        );
        $response->assertOk();

        $status->refresh();
        $this->assertNull($status->body);
        $this->assertNull($status->event_id);
    }

    public function test_status_update_with_changed_destination(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $checkin = Checkin::factory(['user_id' => $user->id])->create();

        // Create a new stopover now (factory creates departure 1 hour ago and arrival in 1 hour)
        $newStation = Station::factory()->create();
        $thirdTimestamp = Date::now()->setSecond(0);
        Stopover::factory([
            'trip_id' => $checkin->trip_id,
            'train_station_id' => $newStation->id,
            'arrival_planned' => $thirdTimestamp,
            'arrival_real' => $thirdTimestamp,
            'departure_planned' => $thirdTimestamp,
            'departure_real' => $thirdTimestamp,
        ])->create();

        $this->assertNotEquals($checkin->originStopover->station->id, $newStation->id);
        $this->assertNotEquals($checkin->destinationStopover->station->id, $newStation->id);

        $response = $this->put(
            uri: '/api/v1/status/' . $checkin->status_id,
            data: [
                'visibility' => StatusVisibility::PUBLIC->value,
                'business' => Business::BUSINESS->value,
                'destinationId' => $newStation->id,
                'destinationArrivalPlanned' => $thirdTimestamp->toDateTimeString(),
            ],
        );
        $response->assertOk();

        $checkin = $checkin->fresh();

        $this->assertEquals($checkin->destinationStopover->station->id, $newStation->id);
    }

    public function test_status_update_change_destination_with_ambiguous_stopover(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $arrivalTime = Date::now()->setSecond(0);

        $otherTrip = Trip::factory()->create();
        $checkin = Checkin::factory(['user_id' => $user->id])->create();

        $newStation = Station::factory()->create();

        Stopover::factory([
            'trip_id' => $otherTrip->trip_id,
            'train_station_id' => $newStation->id,
            'arrival_planned' => $arrivalTime,
            'arrival_real' => $arrivalTime,
            'departure_planned' => $arrivalTime,
            'departure_real' => $arrivalTime,
        ])->create();

        // Add a stopover on the checkin's trip at the same station and time
        // (we had a bug where the query in StatusController::update didn't filter by trip_id,
        // so it could pick up the wrong stopover from another trip)
        $correctStopover = Stopover::factory([
            'trip_id' => $checkin->trip_id,
            'train_station_id' => $newStation->id,
            'arrival_planned' => $arrivalTime,
            'arrival_real' => $arrivalTime,
            'departure_planned' => $arrivalTime,
            'departure_real' => $arrivalTime,
        ])->create();

        $this->assertNotEquals($checkin->trip_id, $otherTrip->trip_id);

        // Try to change the destination to the new station.
        $response = $this->put(
            uri: '/api/v1/status/' . $checkin->status_id,
            data: [
                'visibility' => StatusVisibility::PUBLIC->value,
                'business' => Business::BUSINESS->value,
                'destinationId' => $newStation->id,
                'destinationArrivalPlanned' => $arrivalTime->toDateTimeString(),
            ],
        );
        $response->assertOk();

        $checkin->refresh();
        $this->assertEquals($newStation->id, $checkin->destinationStopover->station->id);
        $this->assertEquals($correctStopover->id, $checkin->destination_stopover_id);
    }

    public function test_status_list_endpoint(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['*']);

        $response = $this->get('/api/v1/status');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
        $response->assertJsonStructure([
            'data' => [],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'path',
                'per_page',
                'next_cursor',
                'prev_cursor',
            ],
        ]);

        $checkin = Checkin::factory(['user_id' => $user->id])->create();
        $checkin->status->update([
            'body' => '#MyFirstJourney',
            'visibility' => StatusVisibility::PUBLIC->value,
        ]);

        // generic list without query
        $response = $this->get('/api/v1/status');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        // query for #MyFirstJourney
        $response = $this->get('/api/v1/status?body=%23MyFirstJourney');
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        // query for something else
        $response = $this->get('/api/v1/status?body=somethingelse');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}
