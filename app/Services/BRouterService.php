<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Coordinate;
use App\Http\Controllers\Backend\VersionController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use RuntimeException;

class BRouterService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('services.brouter.timeout', 30),
            'headers' => [
                'User-Agent' => VersionController::getUserAgent(),
            ],
        ]);
    }

    /**
     * Request a routed path from BRouter for the given waypoints.
     *
     * @param  Coordinate[]  $waypoints  At least two waypoints (origin + destination, optional intermediates)
     * @return array{coordinates: Coordinate[], distance: int, duration: int}
     *
     * @throws GuzzleException
     * @throws RuntimeException|JsonException
     */
    public function getRoute(array $waypoints): array
    {
        if (count($waypoints) < 2) {
            throw new RuntimeException('BRouter requires at least two waypoints.');
        }

        $lonlats = implode('|', array_map(
            static fn (Coordinate $c) => $c->longitude . ',' . $c->latitude,
            $waypoints,
        ));

        $url = sprintf(
            '%s?lonlats=%s&profile=%s&alternativeidx=0&format=geojson',
            rtrim((string) config('services.brouter.url', 'https://brouter.de/brouter'), '/'),
            $lonlats,
            config('services.brouter.profile', 'rail'),
        );

        $response = $this->client->get($url);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('BRouter returned HTTP ' . $response->getStatusCode());
        }

        $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (empty($json['features'])) {
            throw new RuntimeException('BRouter returned no features.');
        }

        $feature = $json['features'][0];

        if (($feature['geometry']['type'] ?? '') !== 'LineString') {
            throw new RuntimeException('BRouter returned unexpected geometry type.');
        }

        $coordinates = [];
        foreach ($feature['geometry']['coordinates'] as $point) {
            // GeoJSON: [lon, lat, (ele)]
            $coordinates[] = new Coordinate((float) $point[1], (float) $point[0]);
        }

        $properties = $feature['properties'] ?? [];
        $distance = (int) ($properties['track-length'] ?? 0);
        // total-time is in seconds
        $duration = (int) ($properties['total-time'] ?? 0);

        return compact('coordinates', 'distance', 'duration');
    }
}
