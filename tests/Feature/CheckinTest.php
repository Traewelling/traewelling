<?php

namespace Tests\Feature;

use App\Dto\CheckinSuccess;
use App\Dto\Internal\CheckInRequestDto;
use App\Enum\Business;
use App\Enum\PointReason;
use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Http\Controllers\Backend\Helper\StatusHelper;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController;
use App\Hydrators\CheckinRequestHydrator;
use App\Models\Station;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\FeatureTestCase;
use Tests\Helpers\CheckinRequestTestHydrator;

class CheckinTest extends FeatureTestCase
{

    use RefreshDatabase;

    private string $plus_one_day_then_8pm = "+1 day 8:00";

    /**
     * Use the stationboard api and check if it works.
     * @test
     */
    public function stationboardTest(): void {
        Http::fake([
                       '/locations*'                => Http::response([self::FRANKFURT_HBF]),
                       '/stops/8000105/departures*' => Http::response([self::ICE802])
                   ]);

        $requestDate = Carbon::parse(self::DEPARTURE_TIME);

        $trainStationboard = TransportController::getDepartures(
            stationQuery: self::FRANKFURT_HBF['name'],
            when:         $requestDate
        );

        $departures = $trainStationboard['departures'];

        $this->assertCount(1, $departures);
        $this->assertEquals(self::TRIP_ID, $departures[0]->tripId);
    }

    /**
     * Test if the checkin collision is truly working
     */
    public function testCheckinCollision(): void {
        // GIVEN: Generate Stations
        Station::factory()->count(4)->create();

        // GIVEN: A logged-in and gdpr-acked user
        $user = User::factory()->create();

        /*
         * We're now generating a 'base checkin' on which we are comparing all possible collision types
         *                 |   |   | 12:00 |   |   | 13:00 |   |   |   |
         *    Base:        |   |   |   |░░░░░░░░░░░░░░░|   |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 1:      |  11:45|▓▓▓▓▓▓▓|12:15  |   |   |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 2:      |   |   |   |   |  12:45|▓▓▓▓▓▓▓|13:15  |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 3:      |   |   |  12:15|▓▓▓▓▓▓▓|12:45  |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 4:      |  11:45|▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓|13:15  |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 5: 11:15|▓▓▓▓▓▓▓|11:45  |   |   |   |   |   |   |   |
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *    Case 6:      |   |   |   |   |   |   |   |  13:30|▓▓▓▓▓▓▓|13:45
         *                 |   |   |   |   |   |   |   |   |   |   |   |
         *
         */

        $collisionTrips    = [];
        $nonCollisionTrips = [];
        $baseTrip          = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('12:00')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('13:00'))
            ]
        );

        //Trips Case 1 - 4 for which a collisionException should be thrown
        $collisionTrips[] = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('11:45')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('12:15'))
            ]
        );
        $collisionTrips[] = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('12:45')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('13:15'))
            ]
        );
        $collisionTrips[] = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('12:15')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('12:45'))
            ]
        );
        $collisionTrips[] = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('11:45')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('13:15'))
            ]
        );

        //Trips case 5 & 6 for which no Exception should be thrown
        $nonCollisionTrips[] = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('11:15')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('11:45'))
            ]
        );
        $nonCollisionTrips[] = Trip::factory()->create(
            [
                'departure' => date('Y-m-d H:i:sP', strtotime('13:30')),
                'arrival'   => date('Y-m-d H:i:sP', strtotime('13:45'))
            ]
        );

        try {
            TrainCheckinController::checkin((new CheckinRequestTestHydrator($user))->hydrateFromTrip($baseTrip));
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $caseCount = 1; //This variable is needed to output error messages in case of a failed test
        foreach ($collisionTrips as $trip) {
            try {
                TrainCheckinController::checkin((new CheckinRequestTestHydrator($user))->hydrateFromTrip($trip));
                $this->fail("Expected exception for Collision Case $caseCount not thrown");
            } catch (CheckInCollisionException $exception) {
                $this->assertEquals($baseTrip->linename, $exception->checkin->trip->first()->linename);
            } catch (HafasException $e) {
                $this->markTestSkipped($e->getMessage());
            }
            $caseCount++;
        }

        //check normal checkin possibility
        foreach ($nonCollisionTrips as $trip) {
            try {
                TrainCheckinController::checkin((new CheckinRequestTestHydrator($user))->hydrateFromTrip($trip));
                $this->assertTrue(true);
            } catch (CheckInCollisionException $exception) {
                $this->assertEquals($baseTrip->linename, $exception->checkin->trip->first()->linename);
                $this->fail("Exception for Case $caseCount thrown even though checkin should happen.");
            } catch (HafasException $e) {
                $this->markTestSkipped($e->getMessage());
            }
            $caseCount++;
        }
    }
}
