<?php

namespace Tests\Feature\Transport;

use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\HafasController;
use App\Http\Controllers\TransportController;
use App\Models\TrainStopover;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendCheckinTest extends TestCase
{

    use RefreshDatabase;


    public function testStationNotOnTripException() {
        $user            = User::factory()->create();
        $stationHannover = HafasController::getTrainStation(8000152);
        $departures      = HafasController::getDepartures(
            station: $stationHannover,
            when:    Carbon::parse('next monday 10:00 am'),
            type:    TravelType::REGIONAL,
        );
        $rawTrip         = $departures->first();
        if ($rawTrip === null) {
            $this->fail('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        $originStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', $stationHannover->ibnr)->first();

        $this->expectException(StationNotOnTripException::class);
        TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: HafasController::getTrainStation(8000001),
            arrival:     $originStopover->departure_planned,
        );
    }

    public function testSwitchedOriginAndDestinationShouldThrowException() {
        $user       = User::factory()->create();
        $station    = HafasController::getTrainStation(8000152);
        $departures = HafasController::getDepartures(
            station: $station,
            when:    Carbon::parse('next monday 10:00 am'),
            type:    TravelType::REGIONAL,
        );
        $rawTrip    = $departures->first();
        if ($rawTrip === null) {
            $this->fail('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        $originStopover      = $hafasTrip->stopoversNew->where('trainStation.ibnr', $station->ibnr)->first();
        $nextStopovers       = $hafasTrip->stopoversNew
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned);
            });
        $destinationStopover = $nextStopovers->first();

        $this->expectException(\InvalidArgumentException::class);
        TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $destinationStopover->trainStation,
            departure:   $destinationStopover->departure_planned,
            destination: $originStopover->trainStation,
            arrival:     $originStopover->arrival_planned,
        );
    }

    public function testDuplicateCheckinsShouldThrowException() {
        $user       = User::factory()->create();
        $station    = HafasController::getTrainStation(8000152);
        $departures = HafasController::getDepartures(
            station: $station,
            when:    Carbon::parse('next monday 10:00 am'),
            type:    TravelType::REGIONAL,
        );
        $rawTrip    = $departures->first();
        if ($rawTrip === null) {
            $this->fail('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        $originStopover      = $hafasTrip->stopoversNew->where('trainStation.ibnr', $station->ibnr)->first();
        $nextStopovers       = $hafasTrip->stopoversNew
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned);
            });
        $destinationStopover = $nextStopovers->first();

        TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        );
        $this->expectException(CheckInCollisionException::class);
        TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        );
    }

    /**
     * Testing checkins where the line forms a ring structure (e.g. Potsdams 603 Bus).
     * Previously, TRWL produced negative trip durations, or unexpected route distances.
     *
     * @see    https://github.com/Traewelling/traewelling/issues/37
     * @author jeyemwey
     */
    public function testCheckinAtBus603Potsdam(): void {
        // First: Get a train that's fine for our stuff
        $timestamp = Carbon::parse("+1 days 10:00");
        try {
            $trainStationboard = TransportController::getDepartures(
                stationQuery: 'Schloss Cecilienhof, Potsdam',
                when:         $timestamp,
                travelType:   TravelType::BUS
            );
        } catch (HafasException $exception) {
            $this->fail($exception->getMessage());
        }

        if (count($trainStationboard['departures']) === 0) {
            $this->fail('Unable to find matching bus.');
        }

        // The bus runs in a 20min interval
        $departure = $trainStationboard['departures'][0];
        self::isCorrectHafasTrip($departure, $timestamp);

        // Third: Get the trip information
        try {
            $hafasTrip = TrainCheckinController::getHafasTrip(
                tripId:   $departure->tripId,
                lineName: $departure->line->name,
                startId:  $departure->stop->location->id
            );
        } catch (HafasException $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

        //Höhenstr., Potsdam
        $originStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', '736140')->first();
        //Rathaus, Potsdam
        $destinationStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', '736160')->last();

        $user = User::factory(['privacy_ack_at' => Carbon::yesterday()])->create();

        // WHEN: User tries to check-in
        $backendResponse = TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->departure_planned,
        );

        $status  = $backendResponse['status'];
        $checkin = $status->trainCheckin;

        // Es wird tatsächlich die zeitlich spätere Station angenommen.
        $this->assertTrue($checkin->arrival > $checkin->departure);
    }

    /**
     * Testing checkins where the line forms a ring structure, e.g. Berlins Ringbahn. The API
     * represents the Ringbahn as a fluid double-ring. If you choose to check-in at Westkreuz in a
     * counter-clockwise driving train, the API will give you the last ring (Südkreuz -
     * Gesundbrunnen - Westkreuz) and the following ring up to Südkreuz (Westkreuz - Südkreuz -
     * Gesundbrunnen - Westkreuz - Südkreuz). If you choose to get into the second ring (e.g. exit
     * at Tempelhof), TRWL has assumed, you meant the Tempelhof the first time it appeared which
     * was negative in time from our trip. This led to negative durations.
     *
     * @author jeyemwey
     * @see    https://github.com/Traewelling/traewelling/issues/37
     */
    public function testCheckinAtBerlinRingbahnRollingOverSuedkreuz(): void {
        if (Carbon::now()->isBefore('2022-04-19 02:00:00')) {
            $this->markTestSkipped('There are currently construction works in Tempelhof. Skipping test. We should work on non real-data tests...');
        }

        // First: Get a train that's fine for our stuff
        // The 10:00 train actually quits at Südkreuz, but the 10:05 does not.
        $station    = HafasController::getTrainStation(8089110);
        $departures = HafasController::getDepartures(
            station: $station,
            when:    Carbon::parse('next monday 10:00 am'),
        );
        $rawTrip    = $departures->where('line.name', 'S 42')
                                 ->first();
        if ($rawTrip === null) {
            $this->markTestSkipped('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        $user = User::factory()->create();

        // Berlin-Westkreuz. We hop in there.
        $originStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', 8089047)->first();
        // Berlin-Tempelhof is 7 stations behind Westkreuz and runs over the Südkreuz mark
        $destinationStopover = $hafasTrip->stopoversNew
            ->where('trainStation.ibnr', 8089090)
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned->clone()->addMinutes(10));
            })
            ->last();

        $response     = TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        );
        $trainCheckin = $response['status']->trainCheckin;

        $this->assertEquals(8089047, $trainCheckin->origin);
        $this->assertEquals(8089090, $trainCheckin->destination);
        $this->assertEquals('S 42', $trainCheckin->HafasTrip->linename);
        $this->assertTrue($trainCheckin->departure->isBefore($trainCheckin->arrival));
    }

    public function testDistanceCalculationOnRingLinesForFirstOccurrence(): void {
        $user                    = User::factory()->create();
        $stationPlantagenPotsdam = HafasController::getTrainStation(736165);
        $departures              = HafasController::getDepartures(
            station: $stationPlantagenPotsdam,
            when:    Carbon::parse('next monday 10:00 am'),
            type:    TravelType::TRAM,
        );
        $rawTrip                 = $departures->where('line.name', 'STR 94')
                                              ->where('direction', 'Schloss Charlottenhof, Potsdam')
                                              ->first();
        if ($rawTrip === null) {
            $this->markTestSkipped('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        // We hop in at Plantagenstr, Potsdam.
        $originStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', 736165)->first();
        // We check out two stations later at Babelsberg (S)/Wattstr., Potsdam.
        $destinationStopover = $hafasTrip->stopoversNew
            ->where('trainStation.ibnr', 736089)
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned);
            })
            ->first();

        $response     = TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        );
        $trainCheckin = $response['status']->trainCheckin;
        $distance     = $trainCheckin->distance;

        //We check, that the distance is between 500 and 1000 meters.
        // This avoids failed tests when the polyline is changed by the EVU.
        $this->assertGreaterThan(500, $distance);
        $this->assertLessThan(1000, $distance);
    }

    public function testDistanceCalculationOnRingLinesForSecondOccurrence(): void {
        $user                    = User::factory()->create();
        $stationPlantagenPotsdam = HafasController::getTrainStation(736165);
        $departures              = HafasController::getDepartures(
            station: $stationPlantagenPotsdam,
            when:    Carbon::parse('next monday 10:00 am'),
        );
        $rawTrip                 = $departures->where('line.name', 'STR 94')
                                              ->where('direction', 'Schloss Charlottenhof, Potsdam')
                                              ->first();
        if ($rawTrip === null) {
            $this->markTestSkipped('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        // We hop in at Plantagenstr, Potsdam.
        $originStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', 736165)->first();
        // We check out at Babelsberg (S)/Wattstr., Potsdam. But this time we go a whole round with.
        $destinationStopover = $hafasTrip->stopoversNew
            ->where('trainStation.ibnr', 736089)
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned->clone()->addMinutes(10));
            })
            ->first();

        $response     = TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        );
        $trainCheckin = $response['status']->trainCheckin;
        $distance     = $trainCheckin->distance;

        //We check, that the distance is between 12000 and 12500 meters.
        // This avoids failed tests when the polyline is changed by the EVU.
        $this->assertGreaterThan(12000, $distance);
        $this->assertLessThan(12500, $distance);
    }

    public function testBusAirAtFrankfurtAirport(): void {
        $user       = User::factory()->create();
        $station    = HafasController::getTrainStation(102932); // Flughafen Terminal 1, Frankfurt a.M.
        $departures = HafasController::getDepartures(
            station: $station,
            when:    Carbon::parse('next monday 10:00 am'),
            type:    TravelType::BUS,
        );
        $rawTrip    = $departures->where('line.name', 'Bus AIR')
                                 ->first();
        if ($rawTrip === null) {
            $this->fail('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        // We hop in at Flughafen Terminal 1, Frankfurt a.M.
        $originStopover = $hafasTrip->stopoversNew->where('trainStation.ibnr', 102932)->first();
        // We check out at Hauptbahnhof, Darmstadt
        $destinationStopover = $hafasTrip->stopoversNew
            ->where('trainStation.ibnr', 104734)
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned->clone()->addMinutes(10));
            })
            ->first();

        $response     = TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        );
        $trainCheckin = $response['status']->trainCheckin;

        $this->assertEquals(102932, $trainCheckin->origin);
        $this->assertEquals(104734, $trainCheckin->destination);
        $this->assertTrue($trainCheckin->departure->isBefore($trainCheckin->arrival));
    }

    public function testChangeTripDestination() {
        $user       = User::factory()->create();
        $station    = HafasController::getTrainStation(8000152);
        $departures = HafasController::getDepartures(
            station: $station,
            when:    Carbon::parse('next monday 10:00 am'),
            type:    TravelType::REGIONAL,
        );
        $rawTrip    = $departures->first();
        if ($rawTrip === null) {
            $this->fail('Unable to find trip.');
        }
        $hafasTrip = HafasController::getHafasTrip($rawTrip->tripId, $rawTrip->line->name);

        $originStopover      = $hafasTrip->stopoversNew->where('trainStation.ibnr', $station->ibnr)->first();
        $nextStopovers       = $hafasTrip->stopoversNew
            ->where(function(TrainStopover $stopover) use ($originStopover) {
                return isset($stopover->arrival_planned)
                       && $stopover->arrival_planned->isAfter($originStopover->departure_planned);
            });
        $destinationStopover = $nextStopovers->first();
        $newStopover         = $nextStopovers->last();

        $status = TrainCheckinController::checkin(
            user:        $user,
            hafasTrip:   $hafasTrip,
            origin:      $originStopover->trainStation,
            departure:   $originStopover->departure_planned,
            destination: $destinationStopover->trainStation,
            arrival:     $destinationStopover->arrival_planned,
        )['status'];

        $this->assertEquals($originStopover->id, $status->trainCheckin->origin_stopover->id);
        $this->assertEquals($destinationStopover->id, $status->trainCheckin->destination_stopover->id);

        TrainCheckinController::changeDestination($status->trainCheckin, $newStopover);

        $this->assertEquals($originStopover->id, $status->trainCheckin->origin_stopover->id);
        $this->assertEquals($newStopover->id, $status->trainCheckin->destination_stopover->id);
    }
}
