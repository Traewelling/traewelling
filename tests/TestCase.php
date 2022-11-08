<?php

namespace Tests;

use App\Enum\StatusVisibility;
use App\Enum\TravelType;
use App\Exceptions\Checkin\AlreadyCheckedInException;
use App\Exceptions\CheckInCollisionException;
use App\Exceptions\HafasException;
use App\Exceptions\NotConnectedException;
use App\Exceptions\StationNotOnTripException;
use App\Exceptions\TrainCheckinAlreadyExistException;
use App\Http\Controllers\Backend\Transport\TrainCheckinController;
use App\Http\Controllers\TransportController;
use App\Models\Event;
use App\Models\HafasTrip;
use App\Models\TrainStation;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use JetBrains\PhpStorm\ArrayShape;
use Tests\Feature\CheckinTest;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void {
        parent::setUp();
        $this->artisan('db:seed --class=Database\\\\Seeders\\\\PrivacyAgreementSeeder');
    }

    public function createGDPRAckedUser(array $defaultValues = []): User {
        $user = User::factory($defaultValues)->create();
        $this->acceptGDPR($user);

        return $user;
    }

    /**
     * @var string Hafas is weird and it's trip ids are shorter the first 9 days of the month.
     */
    private static $HAFAS_ID_DATE = 'jmY';

    /**
     * Check if the given Hafas Trip was correct. Can be used from several test functions.
     * Currently checking if the hafas tripId contains four pipe characters and if it contains the
     * date of the request. If the test runs between 23:45 and midnight, the stationboard response
     * may contain trains starting the next day. If the test runs after midnight it might contain
     * some trains that started the day before.
     *
     * Trips where the first station is a day before the requestDate can be even one day more earlier.
     * e.g. Train starts at 01.01. but out request is on the same train which departs on 02.01 at 00:01
     * at the second station -> in the trip is is still the 01.01.
     *
     * @return Boolean If all checks were resolved positively. Assertions to be made on the caller
     * side to provide a coherent amount of assertions.
     * @throws Exception
     */
    public static function isCorrectHafasTrip($hafastrip, Carbon $requestDate): bool {
        $requestDateMinusMinusOneDay = $requestDate->clone()->subDays(2)->format(self::$HAFAS_ID_DATE);
        $requestDateMinusOneDay      = $requestDate->clone()->subDay()->format(self::$HAFAS_ID_DATE);
        $requestDatePlusOneDay       = $requestDate->clone()->addDay()->format(self::$HAFAS_ID_DATE);
        $requestDate                 = $requestDate->format(self::$HAFAS_ID_DATE);

        // All Hafas Trips should have four pipe characters
        $fourPipes = 4 == substr_count($hafastrip->tripId, '|');

        $rightDate = in_array(1, [
            substr_count($hafastrip->tripId, $requestDateMinusMinusOneDay),
            substr_count($hafastrip->tripId, $requestDateMinusOneDay),
            substr_count($hafastrip->tripId, $requestDate),
            substr_count($hafastrip->tripId, $requestDatePlusOneDay)
        ]);

        $ret = $fourPipes && $rightDate;
        if (!$ret) {
            echo "The following Hafas Trip did not match our expectations:";
            dd($hafastrip);
        }
        return $ret;
    }

    public function acceptGDPR(User $user): void {
        $response = $this->actingAs($user)
                         ->post('/gdpr-ack');
        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
    }


    /**
     * This is mostly copied from Checkin Test and exactly copied from ExportTripsTest.
     *
     * @param string           $stationName
     * @param Carbon           $timestamp
     * @param User|null        $user
     * @param bool|null        $forEvent
     * @param StatusVisibility $statusVisibility
     *
     * @return array|null
     * @throws TrainCheckinAlreadyExistException|NotConnectedException|AlreadyCheckedInException
     */
    #[ArrayShape([
        'success'              => "bool",
        'statusId'             => "int",
        'points'               => "int",
        'alsoOnThisConnection' => Collection::class,
        'lineName'             => "string",
        'distance'             => "float",
        'duration'             => "float",
        'event'                => "mixed"
    ])]
    protected function checkin(
        string           $stationName,
        Carbon           $timestamp,
        User             $user = null,
        bool             $forEvent = null,
        StatusVisibility $statusVisibility = StatusVisibility::PUBLIC
    ): ?array {
        if ($user === null) {
            $user = $this->user;
        }
        try {
            $trainStationboard = TransportController::getDepartures(
                stationQuery: $stationName,
                when:         $timestamp,
                travelType:   TravelType::EXPRESS
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }
        $countDepartures = $trainStationboard['departures']->count();
        if ($countDepartures === 0) {
            $this->markTestSkipped("Unable to find matching trains. Is it night in $stationName?");
        }

        // Second: We don't like broken or cancelled trains.
        $i = 0;
        while ((isset($trainStationboard['departures'][$i]->cancelled)
                && $trainStationboard['departures'][$i]->cancelled)
               || count($trainStationboard['departures'][$i]->remarks) != 0) {
            $i++;
            if ($i == $countDepartures) {
                $this->markTestSkipped("Unable to find unbroken train. Is it stormy in $stationName?");
            }
        }
        $departure = $trainStationboard['departures'][$i];
        CheckinTest::isCorrectHafasTrip($departure, $timestamp);

        // Third: Get the trip information
        try {
            $hafasTrip = TrainCheckinController::getHafasTrip(
                tripId:   $departure->tripId,
                lineName: $departure->line->name,
                startId:  $departure->stop->location->id
            );
        } catch (HafasException $e) {
            $this->markTestSkipped($e->getMessage());
        }

        $eventId = 0;
        if ($forEvent !== null) {
            try {
                $eventId = Event::firstOrFail()->id;
            } catch (ModelNotFoundException) {
                $this->markTestSkipped("No event found even though required");
            }
        }

        // WHEN: User tries to check-in
        try {
            $origin      = $hafasTrip->originStation;
            $departure   = $hafasTrip->departure;
            $destination = $hafasTrip->destinationStation;
            $arrival     = $hafasTrip->arrival;
            $event       = $eventId === null ? null : Event::find($eventId);

            $backendResponse = TrainCheckinController::checkin(
                user:        $user,
                hafasTrip:   $hafasTrip,
                origin:      $origin,
                departure:   $departure,
                destination: $destination,
                arrival:     $arrival,
                visibility:  $statusVisibility,
                event:       $event
            );

            $status       = $backendResponse['status'];
            $trainCheckin = $status->trainCheckin;

            //Legacy return to be compatible with old tests
            return [
                'success'              => true,
                'statusId'             => $status->id,
                'points'               => $trainCheckin->points,
                'alsoOnThisConnection' => $trainCheckin->alsoOnThisConnection,
                'lineName'             => $hafasTrip->linename,
                'distance'             => $trainCheckin->distance,
                'duration'             => $trainCheckin->duration,
                'event'                => $event
            ];
        } catch (StationNotOnTripException) {
            $this->markTestSkipped("failure in checkin creation for " . $stationName . ": Station not in stopovers");
        } catch (CheckInCollisionException) {
            $this->markTestSkipped("failure for " . $timestamp->format('Y-m-d H:i:s') . ": Collision");
        } catch (HafasException $exception) {
            $this->markTestSkipped($exception->getMessage());
        }
    }

    public function checkHafasException(TestResponse $response, int $status = 503): void {
        if ($response->getStatusCode() === $status) {
            $this->markTestIncomplete("HafasException");
        }
    }
}
