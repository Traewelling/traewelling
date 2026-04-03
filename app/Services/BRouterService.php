<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\BRouter\RouteDto;
use App\Dto\Coordinate;
use App\Enum\BRouterProfile;
use App\Exceptions\BRouterException;
use App\Http\Controllers\Backend\VersionController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;

class BRouterService
{
    private Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'timeout' => config('services.brouter.timeout', 5),
            'http_errors' => false,
            'headers' => [
                'User-Agent' => VersionController::getUserAgent(),
            ],
        ]);
    }

    /**
     * Request a routed path from BRouter for the given waypoints.
     *
     * @param  Coordinate[]  $waypoints  At least two waypoints (origin + destination, optional intermediates)
     *
     * @throws BRouterException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function getRoute(array $waypoints, BRouterProfile $profile = BRouterProfile::RAIL): RouteDto
    {
        if (count($waypoints) < 2) {
            throw new BRouterException('BRouter requires at least two waypoints.');
        }

        $json = $this->fetchGeoJson($waypoints, $profile);
        $feature = $this->extractLineStringFeature($json);
        $coordinates = $this->parseCoordinates($feature);

        $this->validateEndpoints($waypoints, $coordinates);

        return new RouteDto(
            coordinates: $coordinates,
            distanceInMeters: (int) ($feature['properties']['track-length'] ?? 0),
        );
    }

    /**
     * @param  Coordinate[]  $waypoints
     *
     * @throws GuzzleException
     * @throws BRouterException
     * @throws JsonException
     */
    private function fetchGeoJson(array $waypoints, BRouterProfile $profile): array
    {
        $lonlats = implode('|', array_map(
            static fn (Coordinate $c) => $c->longitude . ',' . $c->latitude,
            $waypoints,
        ));

        $url = sprintf(
            '%s?lonlats=%s&profile=%s&alternativeidx=0&format=geojson',
            rtrim((string) config('services.brouter.url', 'https://brouter.de/brouter'), '/'),
            $lonlats,
            $profile->value,
        );

        Log::debug('BRouterService: sending request', [
            'url' => $url,
            'profile' => $profile->value,
            'waypoint_count' => count($waypoints),
        ]);

        $response = $this->client->get($url);
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            $body = $response->getBody()->getContents();
            Log::debug('BRouterService: non-200 response', [
                'status' => $statusCode,
                'url' => $url,
                'response_body' => $body,
            ]);
            throw new BRouterException('BRouter returned HTTP ' . $statusCode . ': ' . $body);
        }

        return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @throws BRouterException */
    private function extractLineStringFeature(array $json): array
    {
        if (empty($json['features'])) {
            throw new BRouterException('BRouter returned no features.');
        }

        $feature = $json['features'][0];

        if (($feature['geometry']['type'] ?? '') !== 'LineString') {
            throw new BRouterException(
                'BRouter returned unexpected geometry type: ' . ($feature['geometry']['type'] ?? 'null'),
            );
        }

        return $feature;
    }

    /**
     * @return Coordinate[]
     */
    private function parseCoordinates(array $feature): array
    {
        $coordinates = [];
        foreach ($feature['geometry']['coordinates'] as $point) {
            // GeoJSON: [lon, lat, (ele)]
            $coordinates[] = new Coordinate((float) $point[1], (float) $point[0]);
        }

        return $coordinates;
    }

    /**
     * @param  Coordinate[]  $waypoints
     * @param  Coordinate[]  $coordinates
     *
     * @throws BRouterException
     */
    private function validateEndpoints(array $waypoints, array $coordinates): void
    {
        $tolerance = (int) config('services.brouter.endpoint_tolerance_meters', 200);
        $geoService = new GeoService();

        $distFromStart = $geoService->getDistance($waypoints[0], $coordinates[0]);
        if ($distFromStart > $tolerance) {
            throw new BRouterException(sprintf(
                'BRouter route start is %.0fm from requested start (tolerance: %dm).',
                $distFromStart,
                $tolerance,
            ));
        }

        $distFromEnd = $geoService->getDistance(end($waypoints), end($coordinates));
        if ($distFromEnd > $tolerance) {
            throw new BRouterException(sprintf(
                'BRouter route end is %.0fm from requested end (tolerance: %dm).',
                $distFromEnd,
                $tolerance,
            ));
        }
    }
}
