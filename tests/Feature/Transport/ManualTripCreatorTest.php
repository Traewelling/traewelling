<?php

namespace Feature\Transport;

use App\Dto\Internal\CheckInRequestDto;
use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use App\Http\Controllers\Backend\Transport\ManualTripCreator;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Models\HafasOperator;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class ManualTripCreatorTest extends FeatureTestCase
{

    use RefreshDatabase;

    public function testCanCreateManualTripsAndCheckin(): void {
        $originStation            = Station::factory()->create();
        $stopoverStation          = Station::factory()->create();
        $destinationStation       = Station::factory()->create();
        $departure                = Carbon::now()->addMinutes(5)->setSecond(0)->setMicrosecond(0);
        $stopoverArrivalDeparture = Carbon::now()->addMinutes(10)->setSecond(0)->setMicrosecond(0);
        $arrival                  = Carbon::now()->addMinutes(15)->setSecond(0)->setMicrosecond(0);

        $creator = new ManualTripCreator();

        $creator->setCategory(HafasTravelType::REGIONAL)
                ->setLine('S1', 85001)
                ->setOperator(HafasOperator::factory()->create())
                ->addStopover(
                    station:          $stopoverStation,
                    plannedDeparture: $stopoverArrivalDeparture,
                    plannedArrival:   $stopoverArrivalDeparture,
                    realDeparture:    $stopoverArrivalDeparture->clone()->addMinute(),
                    realArrival:      $stopoverArrivalDeparture->clone()->addMinute(),
                )
                ->setOrigin($originStation, $departure)
                ->setDestination($destinationStation, $arrival);

        $trip = $creator->createFullTrip();
        $trip->refresh();

        $this->assertEquals(TripSource::USER, $trip->source);

        $this->assertDatabaseHas('hafas_trips', [
            'trip_id'        => $trip->trip_id,
            'category'       => $trip->category,
            'number'         => $trip->number,
            'linename'       => $trip->linename,
            'journey_number' => $trip->journey_number,
            'operator_id'    => $trip->operator_id,
            'origin_id'      => $trip->origin_id,
            'destination_id' => $trip->destination_id,
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

        $dto = new CheckInRequestDto();
        $dto->setUser(User::factory()->create())
            ->setTrip($trip)
            ->setOrigin($originStation)
            ->setDeparture($departure)
            ->setDestination($destinationStation)
            ->setArrival($arrival);
        $checkin = TrainCheckinController::checkin($dto);

        $this->assertDatabaseHas('train_checkins', [
            'trip_id' => $trip->trip_id,
            'user_id' => $checkin->status->checkin->user_id,
        ]);
    }
}
