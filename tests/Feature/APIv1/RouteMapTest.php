<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Enum\Business;
use App\Enum\HafasTravelType;
use App\Models\Checkin;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\Stopover;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class RouteMapTest extends ApiTestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/v1/route-map';

    public function test_route_map_returns_every_travelled_stretch_exactly_once(): void
    {
        $user = User::factory()->create();
        $foreignUser = User::factory()->create();

        $trip = $this->createJourney($user, routedLegs: 3);
        // Träwelling the same stretches again must not duplicate them on the map.
        $this->checkinOnTrip($trip, $foreignUser, business: Business::PRIVATE);
        $this->createJourney($foreignUser, routedLegs: 2);

        Passport::actingAs($user, ['*']);
        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                ['routeSegmentId', 'fromStation', 'toStation', 'polyline', 'polylinePrecision', 'distance', 'pathType', 'categories', 'approximated'],
            ],
            'meta' => ['count', 'approximatedCount', 'distance'],
        ]);

        $this->assertSame(3, $response->json('meta.count'));
        $this->assertSame(0, $response->json('meta.approximatedCount'));
        $this->assertSame(3 * 1000, $response->json('meta.distance'));

        foreach ($response->json('data') as $entry) {
            $this->assertFalse($entry['approximated']);
            $this->assertNotNull($entry['routeSegmentId']);
            $this->assertNull($entry['fromStation']);
            $this->assertSame([HafasTravelType::REGIONAL->value], $entry['categories']);
        }
    }

    public function test_route_map_reports_all_modes_of_transport_a_stretch_was_travelled_with(): void
    {
        $user = User::factory()->create();

        $segment = $this->createSegment();
        $this->createJourney($user, routedLegs: 3, category: HafasTravelType::BUS, segments: [$segment]);
        $this->createJourney($user, routedLegs: 3, category: HafasTravelType::TRAM, segments: [$segment]);

        Passport::actingAs($user, ['*']);
        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk();
        $entry = collect($response->json('data'))->firstWhere('routeSegmentId', $segment->id);
        $this->assertNotNull($entry);
        $this->assertSame([HafasTravelType::BUS->value, HafasTravelType::TRAM->value], $entry['categories']);
    }

    public function test_route_map_can_be_filtered_by_type_purpose_and_time(): void
    {
        $user = User::factory()->create();

        $this->createJourney($user, routedLegs: 1, category: HafasTravelType::BUS, business: Business::PRIVATE, departure: Carbon::parse('2024-03-01 08:00'));
        $this->createJourney($user, routedLegs: 1, category: HafasTravelType::TRAM, business: Business::BUSINESS, departure: Carbon::parse('2025-03-01 08:00'));
        $this->createJourney($user, routedLegs: 1, category: HafasTravelType::REGIONAL, business: Business::COMMUTE, departure: Carbon::parse('2026-03-01 08:00'));

        Passport::actingAs($user, ['*']);

        // Every journey contributes exactly one routed stretch, so the counts stay readable.
        $this->assertSame(3, $this->countWithFilter(''));
        $this->assertSame(1, $this->countWithFilter('travelTypes=tram'));
        $this->assertSame(2, $this->countWithFilter('travelTypes[]=tram&travelTypes[]=bus'));
        $this->assertSame(1, $this->countWithFilter('travelPurposes=1'));
        // Purposes are combined with OR: business trips *and* commutes.
        $this->assertSame(2, $this->countWithFilter('travelPurposes=1,2'));
        $this->assertSame(2, $this->countWithFilter('from=2025-01-01'));
        $this->assertSame(2, $this->countWithFilter('until=2025-12-31'));
        $this->assertSame(1, $this->countWithFilter('from=2025-01-01&until=2025-12-31'));
        // All criteria are combined with AND.
        $this->assertSame(0, $this->countWithFilter('travelTypes=bus&travelPurposes=1'));
    }

    private function countWithFilter(string $query): int
    {
        return $this->getJson(self::ENDPOINT . '?includeApproximated=false&' . $query)->json('meta.count');
    }

    public function test_route_map_draws_straight_lines_for_stretches_without_a_route_segment(): void
    {
        $user = User::factory()->create();
        $this->createJourney($user, routedLegs: 1);

        Passport::actingAs($user, ['*']);
        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk();
        $this->assertSame(3, $response->json('meta.count'));
        $this->assertSame(2, $response->json('meta.approximatedCount'));

        $approximated = collect($response->json('data'))->firstWhere('approximated', true);
        $this->assertNull($approximated['routeSegmentId']);
        $this->assertNotNull($approximated['fromStation']);
        $this->assertNotNull($approximated['toStation']);
        $this->assertNotEmpty($approximated['polyline']);
        $this->assertGreaterThan(0, $approximated['distance']);

        $onlyRouted = $this->getJson(self::ENDPOINT . '?includeApproximated=false');
        $this->assertSame(1, $onlyRouted->json('meta.count'));
        $this->assertSame(0, $onlyRouted->json('meta.approximatedCount'));
    }

    public function test_route_map_only_contains_the_part_of_the_trip_the_user_travelled(): void
    {
        $user = User::factory()->create();
        $trip = $this->createJourney(User::factory()->create(), routedLegs: 3);
        $stopovers = $trip->stopovers;

        // Board at the second and alight at the third stop: only the middle leg was träwelled.
        $checkin = Checkin::factory()->create([
            'user_id' => $user->id,
            'trip_id' => $trip->trip_id,
            'origin_stopover_id' => $stopovers[1]->id,
            'destination_stopover_id' => $stopovers[2]->id,
            'departure' => $stopovers[1]->departure_planned,
            'arrival' => $stopovers[2]->arrival_planned,
        ]);
        $checkin->status->update(['user_id' => $user->id]);

        Passport::actingAs($user, ['*']);
        $response = $this->getJson(self::ENDPOINT);

        $response->assertOk();
        $this->assertSame(1, $response->json('meta.count'));
        $this->assertSame($stopovers[1]->route_segment_id, $response->json('data.0.routeSegmentId'));
    }

    public function test_route_map_rejects_unauthenticated_requests_and_invalid_filters(): void
    {
        $this->getJson(self::ENDPOINT)->assertUnauthorized();

        Passport::actingAs(User::factory()->create(), ['*']);
        $this->getJson(self::ENDPOINT . '?travelTypes=spaceship')->assertUnprocessable();
        $this->getJson(self::ENDPOINT . '?travelPurposes=42')->assertUnprocessable();
        $this->getJson(self::ENDPOINT . '?from=2025-01-01&until=2024-01-01')->assertUnprocessable();
    }

    /**
     * Creates a trip with four stopovers (three legs), assigns route segments to the first
     * `$routedLegs` of them and checks the user in from the first to the last stopover.
     *
     * @param  RouteSegment[]  $segments  Segments to assign instead of freshly created ones.
     */
    private function createJourney(
        User $user,
        int $routedLegs = 3,
        HafasTravelType $category = HafasTravelType::REGIONAL,
        Business $business = Business::PRIVATE,
        ?Carbon $departure = null,
        array $segments = [],
    ): Trip {
        $departure ??= Carbon::parse('2025-01-01 08:00');
        $stations = Station::factory()->count(4)->create();

        $trip = Trip::factory()->create([
            'category' => $category->value,
            'origin_id' => $stations->first()->id,
            'destination_id' => $stations->last()->id,
            'departure' => $departure,
            'arrival' => $departure->clone()->addHour(),
        ]);
        Stopover::where('trip_id', $trip->trip_id)->delete();

        foreach ($stations as $index => $station) {
            $time = $departure->clone()->addMinutes($index * 20);
            Stopover::factory()->create([
                'trip_id' => $trip->trip_id,
                'train_station_id' => $station->id,
                'arrival_planned' => $index === 0 ? null : $time,
                'departure_planned' => $index === $stations->count() - 1 ? null : $time,
                'route_segment_id' => $index < $routedLegs
                    ? ($segments[$index] ?? $this->createSegment($station, $stations[$index + 1]))->id
                    : null,
            ]);
        }

        $trip = $trip->fresh();
        $this->checkinOnTrip($trip, $user, business: $business);

        // The check-in factory backfills the times of the origin and destination stopover, which
        // would hide the very case we want covered here: the first stop of a trip has no planned
        // arrival and the last one no planned departure.
        $stopovers = $trip->stopovers;
        $stopovers->first()->update(['arrival_planned' => null, 'arrival_real' => null]);
        $stopovers->last()->update(['departure_planned' => null, 'departure_real' => null]);

        return $trip;
    }

    private function createSegment(?Station $from = null, ?Station $to = null): RouteSegment
    {
        return RouteSegment::factory()->create([
            'from_station_id' => ($from ?? Station::factory()->create())->id,
            'to_station_id' => ($to ?? Station::factory()->create())->id,
            'distance' => 1000,
            'path_type' => 'rail',
        ]);
    }
}
