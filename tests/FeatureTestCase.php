<?php

namespace Tests;

use App\Http\Controllers\Backend\WebhookController;
use App\Models\OAuthClient;
use App\Models\User;
use App\Models\Webhook;
use App\Repositories\OAuthClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class FeatureTestCase extends BaseTestCase
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

        if (!in_array(RefreshDatabase::class, class_uses($this), true)) {
            //if class doesn't use RefreshDatabase trait, skip the migration and seeding
            return;
        }

        $this->artisan('db:seed --class=Database\\\\Seeders\\\\Constants\\\\PermissionSeeder');
        $this->artisan('db:seed --class=Database\\\\Seeders\\\\PrivacyAgreementSeeder');
    }

    public function createWebhookClient(User $user): OAuthClient {
        return (new OAuthClientRepository())->create(
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
        return (new OAuthClientRepository())->create(
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
        $events  = array_map(function($event) {
            return $event->value;
        }, $events);
        $request = WebhookController::createWebhookRequest($user, $client, 'stub', "https://example.com", $events);
        return WebhookController::createWebhook($request);
    }
}
