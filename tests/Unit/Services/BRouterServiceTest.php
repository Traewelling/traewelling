<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Dto\Coordinate;
use App\Enum\BRouterProfile;
use App\Exceptions\BRouterException;
use App\Services\BRouterService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\UnitTestCase;

class BRouterServiceTest extends UnitTestCase
{
    // Hannover Hbf, used as start in most tests
    private const float START_LAT = 52.3766;

    private const float START_LON = 9.7413;

    // Hannover Kröpcke, ~289 m from Hbf
    private const float END_LAT = 52.3744;

    private const float END_LON = 9.7385;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.brouter.enabled' => true]);
    }

    /**
     * Build a BRouterService backed by a Guzzle MockHandler.
     *
     * @param  array<Response|\Exception>  $responses
     * @param  array<mixed>  $history  Will be filled with request/response pairs
     */
    private function makeService(array $responses, array &$history = []): BRouterService
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        return new BRouterService(new Client(['handler' => $stack, 'http_errors' => false]));
    }

    /**
     * Build a minimal valid BRouter GeoJSON payload.
     *
     * @param  array<array{float, float}|array{float, float, float}>  $coords  [lon, lat] or [lon, lat, ele] pairs
     */
    private function makeGeoJson(array $coords, int|string $trackLength = 1234): string
    {
        return json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $coords,
                    ],
                    'properties' => [
                        'track-length' => (string) $trackLength,
                    ],
                ],
            ],
        ]);
    }

    /** Waypoints that sit right on the start and end coords used in fixtures. */
    private function defaultWaypoints(): array
    {
        return [
            new Coordinate(self::START_LAT, self::START_LON),
            new Coordinate(self::END_LAT, self::END_LON),
        ];
    }

    /** A two-coordinate route that starts at START and ends at END (within tolerance). */
    private function defaultCoords(): array
    {
        return [
            [self::START_LON, self::START_LAT],
            [self::END_LON, self::END_LAT],
        ];
    }

    public function test_returns_route_dto_with_correct_coordinates_and_distance(): void
    {
        $coords = [
            [self::START_LON, self::START_LAT],
            [9.7400, 52.3755],
            [self::END_LON, self::END_LAT],
        ];

        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($coords, 5678))]);
        $route = $service->getRoute($this->defaultWaypoints());

        $this->assertCount(3, $route->coordinates);
        $this->assertSame(5678, $route->distanceInMeters);

        // GeoJSON [lon, lat] → Coordinate(lat, lon)
        $this->assertSame(self::START_LAT, $route->coordinates[0]->latitude);
        $this->assertSame(self::START_LON, $route->coordinates[0]->longitude);
        $this->assertSame(self::END_LAT, $route->coordinates[2]->latitude);
        $this->assertSame(self::END_LON, $route->coordinates[2]->longitude);
    }

    public function test_elevation_in_geojson_coordinate_is_ignored(): void
    {
        $coords = [
            [self::START_LON, self::START_LAT, 123.4],
            [self::END_LON, self::END_LAT, 456.7],
        ];

        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($coords))]);
        $route = $service->getRoute($this->defaultWaypoints());

        $this->assertCount(2, $route->coordinates);
        $this->assertSame(self::START_LAT, $route->coordinates[0]->latitude);
        $this->assertSame(self::START_LON, $route->coordinates[0]->longitude);
    }

    public function test_missing_track_length_defaults_to_zero(): void
    {
        $json = json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $this->defaultCoords(),
                    ],
                    'properties' => [],  // no track-length key
                ],
            ],
        ]);

        $service = $this->makeService([new Response(200, [], $json)]);
        $route = $service->getRoute($this->defaultWaypoints());

        $this->assertSame(0, $route->distanceInMeters);
    }

    public function test_road_profile_is_sent_in_request_url(): void
    {
        $history = [];
        $service = $this->makeService(
            [new Response(200, [], $this->makeGeoJson($this->defaultCoords()))],
            $history,
        );

        $service->getRoute($this->defaultWaypoints(), BRouterProfile::ROAD);

        $uri = (string) $history[0]['request']->getUri();
        $this->assertStringContainsString('profile=car-fast', $uri);
    }

    public function test_waypoints_are_encoded_as_lonlats_in_request(): void
    {
        $history = [];
        $service = $this->makeService(
            [new Response(200, [], $this->makeGeoJson($this->defaultCoords()))],
            $history,
        );

        $service->getRoute($this->defaultWaypoints());

        // Guzzle percent-encodes '|' → '%7C' in the URI
        $uri = rawurldecode((string) $history[0]['request']->getUri());
        $this->assertStringContainsString(
            'lonlats=' . self::START_LON . ',' . self::START_LAT
            . '|' . self::END_LON . ',' . self::END_LAT,
            $uri,
        );
    }

    public function test_throws_for_fewer_than_two_waypoints(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter requires at least two waypoints.');

        $service = $this->makeService([]);
        $service->getRoute([new Coordinate(52.0, 9.0)]);
    }

    public function test_throws_for_non_200_response(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter returned HTTP 500');

        $service = $this->makeService([new Response(500, [], 'Internal Server Error')]);
        $service->getRoute($this->defaultWaypoints());
    }

    public static function nonOkStatusProvider(): array
    {
        return [
            '404' => [404],
            '503' => [503],
        ];
    }

    #[DataProvider('nonOkStatusProvider')]
    public function test_throws_for_various_non_200_statuses(int $status): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage("BRouter returned HTTP {$status}");

        $service = $this->makeService([new Response($status)]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_throws_when_features_array_is_empty(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter returned no features.');

        $json = json_encode(['type' => 'FeatureCollection', 'features' => []]);
        $service = $this->makeService([new Response(200, [], $json)]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_throws_when_geometry_type_is_not_linestring(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter returned unexpected geometry type: Point');

        $json = json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [9.0, 52.0]],
                    'properties' => [],
                ],
            ],
        ]);
        $service = $this->makeService([new Response(200, [], $json)]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_throws_when_geometry_type_key_is_missing(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter returned unexpected geometry type: null');

        $json = json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'geometry' => ['coordinates' => []],  // no "type" key
                    'properties' => [],
                ],
            ],
        ]);
        $service = $this->makeService([new Response(200, [], $json)]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_throws_when_route_start_is_too_far_from_requested_start(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter route start is');
        $this->expectExceptionMessage('from requested start');

        // Route starts in Karlsruhe (~380 km from Hannover)
        $coords = [
            [8.4010, 48.9939],      // Karlsruhe, far from START (Hannover)
            [self::END_LON, self::END_LAT],
        ];

        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($coords))]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_throws_when_route_end_is_too_far_from_requested_end(): void
    {
        $this->expectException(BRouterException::class);
        $this->expectExceptionMessage('BRouter route end is');
        $this->expectExceptionMessage('from requested end');

        // Route ends in Karlsruhe (~380 km from Hannover Kröpcke)
        $coords = [
            [self::START_LON, self::START_LAT],
            [8.4010, 48.9939],      // Karlsruhe, far from END (Hannover)
        ];

        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($coords))]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_custom_tolerance_is_respected(): void
    {
        // END is ~310 m from START. With tolerance=400 the offset must be accepted.
        config(['services.brouter.endpoint_tolerance_meters' => 400]);

        $coords = [
            [self::END_LON, self::END_LAT],  // ~310 m from START waypoint
            [self::END_LON, self::END_LAT],
        ];

        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($coords))]);
        $route = $service->getRoute($this->defaultWaypoints());
        $this->assertNotNull($route);

        config(['services.brouter.endpoint_tolerance_meters' => 200]);
    }

    public function test_default_tolerance_rejects_offset_beyond_200m(): void
    {
        // Default tolerance is 200 m. END is ~310 m from START → must be rejected.
        $this->expectException(BRouterException::class);

        $coords = [
            [self::END_LON, self::END_LAT],  // ~310 m away from START waypoint
            [self::END_LON, self::END_LAT],
        ];

        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($coords))]);
        $service->getRoute($this->defaultWaypoints());
    }

    public function test_is_enabled_follows_the_configuration(): void
    {
        config(['services.brouter.enabled' => false]);
        $this->assertFalse($this->makeService([])->isEnabled());

        config(['services.brouter.enabled' => true]);
        $this->assertTrue($this->makeService([])->isEnabled());
    }

    public function test_disabled_service_throws_before_sending_a_request(): void
    {
        config(['services.brouter.enabled' => false]);

        $history = [];
        $service = $this->makeService([new Response(200, [], $this->makeGeoJson($this->defaultCoords()))], $history);

        try {
            $service->getRoute($this->defaultWaypoints());
            $this->fail('Expected a BRouterException for the disabled service.');
        } catch (BRouterException $exception) {
            $this->assertStringContainsString('disabled', $exception->getMessage());
        }

        // The whole point of the switch: no socket is opened, so no connection error can be logged.
        $this->assertEmpty($history);
    }
}
