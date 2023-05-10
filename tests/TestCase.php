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
use App\Http\Controllers\Backend\WebhookController;
use App\Http\Controllers\TransportController;
use App\Models\Event;
use App\Models\OAuthClient;
use App\Models\User;
use App\Models\Webhook;
use App\Repositories\OAuthClientRepository;
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

    const AACHEN_HBF    = [
        "type"     => "stop",
        "id"       => "8000001",
        "name"     => "Aachen Hbf",
        "location" => [
            "type"      => "location",
            "id"        => "8000105",
            "latitude"  => 50.767641,
            "longitude" => 6.09119,
        ]
    ];
    const FRANKFURT_HBF = [
        "type"     => "stop",
        "id"       => "8000105",
        "name"     => "Frankfurt(Main)Hbf",
        "location" => [
            "type"      => "location",
            "id"        => "8000105",
            "latitude"  => 50.106817,
            "longitude" => 8.663003,
        ]
    ];
    const HANNOVER_HBF  = [
        "type"     => "stop",
        "id"       => 8000152,
        "name"     => "Hannover Hbf",
        "ril100"   => "HH",
        "location" => [
            "type"      => "location",
            "id"        => "8000152",
            "latitude"  => 52.377079,
            "longitude" => 9.741763
        ]
    ];

    const DEPARTURE_TIME = "2023-01-12T08:09:00+01:00";
    const STOPOVER_TIME  = "2023-01-12T09:32:00+01:00";
    const ARRIVAL_TIME   = "2023-01-12T10:46:00+01:00";

    const TRIP_ID = "1|154966|0|81|12012023";
    const ICE802  = [
        "tripId"          => self::TRIP_ID,
        "stop"            => self::FRANKFURT_HBF,
        "when"            => self::DEPARTURE_TIME,
        "plannedWhen"     => self::DEPARTURE_TIME,
        "delay"           => null,
        "platform"        => "8",
        "plannedPlatform" => "8",
        "prognosisType"   => null,
        "direction"       => self::HANNOVER_HBF['name'],
        "provenance"      => null,
        "line"            => [
            "type"        => "line",
            "id"          => "ice-822",
            "fahrtNr"     => "822",
            "name"        => "ICE 822",
            "public"      => true,
            "adminCode"   => "80____",
            "productName" => "ICE",
            "mode"        => "train",
            "product"     => "nationalExpress",
            "operator"    => [
                "type" => "operator",
                "id"   => "db-fernverkehr-ag",
                "name" => "DB Fernverkehr AG",
            ],
        ],
        "remarks"         => [],
        "origin"          => null,
        "destination"     => self::HANNOVER_HBF,
    ];

    const TRIP_INFO = [
        "origin"           => self::FRANKFURT_HBF,
        "destination"      => self::HANNOVER_HBF,
        "departure"        => self::DEPARTURE_TIME,
        "plannedDeparture" => self::DEPARTURE_TIME,
        "departureDelay"   => null,
        "arrival"          => self::ARRIVAL_TIME,
        "plannedArrival"   => self::ARRIVAL_TIME,
        "arrivalDelay"     => null,
        "polyline"         => ["features" => []],
        "line"             => self::ICE802['line'],
        "direction"        => self::HANNOVER_HBF['name'],
        "stopovers"        => [
            [
                'arrival'                  => null,
                'plannedArrival'           => null,
                'plannedArrivalPlatform'   => null,
                'departure'                => self::DEPARTURE_TIME,
                'departurePlatform'        => 19,
                'plannedDeparture'         => self::DEPARTURE_TIME,
                'plannedDeparturePlatform' => 19,
                'stop'                     => self::FRANKFURT_HBF
            ],
            [
                'arrival'                  => self::STOPOVER_TIME,
                'plannedArrival'           => self::STOPOVER_TIME,
                'plannedArrivalPlatform'   => null,
                'departure'                => self::STOPOVER_TIME,
                'departurePlatform'        => 4,
                'plannedDeparture'         => self::STOPOVER_TIME,
                'plannedDeparturePlatform' => 5,
                'stop'                     => self::AACHEN_HBF
            ],
            [
                'arrival'                  => self::ARRIVAL_TIME,
                'plannedArrival'           => self::ARRIVAL_TIME,
                'plannedArrivalPlatform'   => 9,
                'departure'                => null,
                'departurePlatform'        => null,
                'plannedDeparture'         => null,
                'plannedDeparturePlatform' => null,
                'stop'                     => self::HANNOVER_HBF
            ],
        ]
    ];

    const EXAMPLE_BODY        = 'Example Body';
    const EXAMPLE_WEBHOOK_URL = 'https://example.com/webhook';

    protected function setUp(): void {
        parent::setUp();
        $this->artisan('db:seed --class=Database\\\\Seeders\\\\PrivacyAgreementSeeder');
    }

    public function createGDPRAckedUser(array $defaultValues = []): User {
        $user = User::factory($defaultValues)->create();
        $this->acceptGDPR($user);

        return $user;
    }

    protected function createAdminUser(): User {
        $admin       = $this->createGDPRAckedUser();
        $admin->role = 10;
        $admin->update();

        return $admin;
    }

    public function createWebhookClient(User $user): OAuthClient {
        $clients = new OAuthClientRepository();
        return $clients->create(
            $user->id,
            "TRWL Webhook Testing Application",
            "https://example.com",
            null,
            false,
            false,
            true,
            "https://example.com/privacy",
            true,
            self::EXAMPLE_WEBHOOK_URL
        );
    }

    public function createOAuthClient(User $user, bool $confidential): OAuthClient {
        $clients = new OAuthClientRepository();
        return $clients->create(
            $user->id,
            "TRWL OAuth Testing Application",
            "https://example.com",
            null,
            false,
            false,
            $confidential,
            "https://example.com/privacy",
            false,
            null,
        );
    }

    public function createWebhook(User $user, OAuthClient $client, array $events): Webhook {
        $bitflag = 0;
        foreach ($events as $event) {
            $bitflag |= $event->value;
        }
        $request = WebhookController::createWebhookRequest($user, $client, 'stub', "https://example.com", $bitflag);
        return WebhookController::createWebhook($request);
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
               || count($trainStationboard['departures'][$i]->remarks) != 0
        ) {
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
