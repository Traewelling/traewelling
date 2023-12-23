<?php

namespace Feature\Transport;

use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualTripCreatorTest extends TestCase
{

    use RefreshDatabase;

    public function testCanCreateManualTripsAndCheckin(): void {
        $originStation      = Station::factory()->create();
        $destinationStation = Station::factory()->create();
        $departure          = Carbon::now()->addMinutes(5)->setSecond(0)->setMicrosecond(0);
        $arrival            = Carbon::now()->addMinutes(15)->setSecond(0)->setMicrosecond(0);

        $creator = new ManualTripCreator();

        $creator->category                  = HafasTravelType::REGIONAL;
        $creator->lineName                  = 'S1';
        $creator->journeyNumber             = 85001;
        $creator->operator                  = HafasOperator::factory()->create();
        $creator->origin                    = $originStation;
        $creator->originDeparturePlanned    = $departure;
        $creator->destination               = $destinationStation;
        $creator->destinationArrivalPlanned = $arrival;

        $trip = $creator->createTrip();
        $creator->createOriginStopover();
        $creator->createDestinationStopover();
        $trip->refresh();

        $this->assertEquals($trip->source, TripSource::USER);

        $this->assertDatabaseHas('hafas_trips', [
            'trip_id'        => $trip->trip_id,
            'category'       => $trip->category,
            'number'         => $trip->number,
            'linename'       => $trip->linename,
            'journey_number' => $trip->journey_number,
            'operator_id'    => $trip->operator_id,
            'origin'         => $trip->origin,
            'destination'    => $trip->destination,
            'departure'      => $trip->departure,
            'arrival'        => $trip->arrival,
            'source'         => $trip->source,
        ]);
        $this->assertDatabaseHas('train_stopovers', [
            'trip_id'           => $trip->trip_id,
            'train_station_id'  => $originStation->id,
            'arrival_planned'   => $departure,
            'departure_planned' => $departure,
        ]);
        $this->assertDatabaseHas('train_stopovers', [
            'trip_id'           => $trip->trip_id,
            'train_station_id'  => $destinationStation->id,
            'arrival_planned'   => $arrival,
            'departure_planned' => $arrival,
        ]);

        /**** Checkin ****/

        $checkin = TrainCheckinController::checkin(
            user:        User::factory()->create(),
            hafasTrip:   $trip,
            origin:      $originStation,
            departure:   $departure,
            destination: $destinationStation,
            arrival:     $arrival
        );

        $this->assertDatabaseHas('train_checkins', [
            'trip_id' => $trip->trip_id,
            'user_id' => $checkin['status']->checkin->user_id,
        ]);
    }
}
