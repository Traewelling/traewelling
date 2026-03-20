<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\Operator;
use App\Models\Station;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class CreateTripTest extends ApiTestCase
{
    use RefreshDatabase;

    private Station $origin;

    private Station $destination;

    private string $departure;

    private string $arrival;

    protected function setUp(): void
    {
        parent::setUp();

        $this->origin = Station::factory()->create();
        $this->destination = Station::factory()->create();
        $this->departure = Carbon::now()->addMinutes(5)->toIso8601String();
        $this->arrival = Carbon::now()->addMinutes(65)->toIso8601String();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'category' => 'regional',
            'lineName' => 'RE 1',
            'originId' => $this->origin->id,
            'originDeparturePlanned' => $this->departure,
            'destinationId' => $this->destination->id,
            'destinationArrivalPlanned' => $this->arrival,
        ], $overrides);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/trips', $this->validPayload());
        $response->assertUnauthorized();
    }

    public function test_user_with_disallow_manual_trips_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('disallow-manual-trips');
        Passport::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/trips', $this->validPayload());
        $response->assertForbidden();
    }

    public function test_create_trip_succeeds_with_valid_payload(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload());

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'category',
                'lineName',
                'journeyNumber',
                'origin' => ['id', 'name'],
                'destination' => ['id', 'name'],
                'stopovers',
            ],
        ]);
        $response->assertJsonPath('data.lineName', 'RE 1');
        $response->assertJsonPath('data.category', 'regional');
        $response->assertJsonPath('data.origin.id', $this->origin->id);
        $response->assertJsonPath('data.destination.id', $this->destination->id);
    }

    public function test_create_trip_with_optional_operator(): void
    {
        $this->actAsApiUserWithAllScopes();
        $operator = Operator::factory()->create();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'operatorId' => $operator->id,
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('hafas_trips', [
            'operator_id' => $operator->id,
        ]);
    }

    public function test_create_trip_with_stopovers(): void
    {
        $this->actAsApiUserWithAllScopes();
        $stopover = Station::factory()->create();
        $stopoverTime = Carbon::now()->addMinutes(30)->toIso8601String();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'stopovers' => [
                [
                    'stationId' => $stopover->id,
                    'arrival' => $stopoverTime,
                    'departure' => $stopoverTime,
                ],
            ],
        ]));

        $response->assertCreated();
        $this->assertDatabaseHas('train_stopovers', [
            'train_station_id' => $stopover->id,
        ]);
    }

    public function test_create_trip_without_stopovers(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'stopovers' => [],
        ]));

        $response->assertCreated();
    }

    public function test_create_trip_fails_when_category_is_missing(): void
    {
        $this->actAsApiUserWithAllScopes();

        $payload = $this->validPayload();
        unset($payload['category']);

        $response = $this->postJson('/api/v1/trips', $payload);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_create_trip_fails_when_line_name_is_missing(): void
    {
        $this->actAsApiUserWithAllScopes();

        $payload = $this->validPayload();
        unset($payload['lineName']);

        $response = $this->postJson('/api/v1/trips', $payload);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['lineName']);
    }

    public function test_create_trip_fails_when_origin_does_not_exist(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'originId' => 99999999,
        ]));
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['originId']);
    }

    public function test_create_trip_fails_when_destination_does_not_exist(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'destinationId' => 99999999,
        ]));
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['destinationId']);
    }

    public function test_create_trip_fails_when_category_is_invalid(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'category' => 'helicopter',
        ]));
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['category']);
    }

    public function test_create_trip_fails_when_operator_does_not_exist(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'operatorId' => 99999999,
        ]));
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['operatorId']);
    }

    public function test_create_trip_fails_when_duration_exceeds_maximum(): void
    {
        $this->actAsApiUserWithAllScopes();

        $response = $this->postJson('/api/v1/trips', $this->validPayload([
            'destinationArrivalPlanned' => Carbon::now()->addHours(49)->toIso8601String(),
        ]));
        $response->assertBadRequest();
    }

    public function test_create_trip_stores_trip_in_database(): void
    {
        $this->actAsApiUserWithAllScopes();

        $this->postJson('/api/v1/trips', $this->validPayload([
            'lineName' => 'ICE 999',
            'journeyNumber' => 999,
        ]))->assertCreated();

        $this->assertDatabaseHas('hafas_trips', [
            'linename' => 'ICE 999',
            'journey_number' => 999,
        ]);
    }
}
