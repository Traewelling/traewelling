<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Coordinate;
use App\Dto\GeoJson\Feature;
use App\Dto\OpenRailRouting\RouteDto;
use App\Exceptions\OpenRailRoutingResponseFailed;
use App\Http\Controllers\Backend\VersionController;
use App\OpenRailRoutingProfile;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use phpGPX\Models\GpxFile;
use phpGPX\Models\Point;
use phpGPX\Models\Segment;
use phpGPX\Models\Track;
use Traewelling\GooglePolyline\PolylineTranscoder;

class OpenRailRoutingService
{
    private const string API_URL = 'https://routing.openrailrouting.org';

    private const int TIMEOUT = 30;

    private Client $client;

    private function initClient(): void
    {
        $this->client = new Client([
            'base_uri' => self::API_URL,
            'timeout' => self::TIMEOUT,
            'cookies' => true,
            'headers' => [
                'User-Agent' => VersionController::getUserAgent(),
            ],
        ]);
    }

    private function getClient(): Client
    {
        if (!isset($this->client)) {
            $this->initClient();
        }

        return $this->client;
    }

    /**
     * @param  Point[]  $coordinates
     *
     * @throws GuzzleException
     * @throws OpenRailRoutingResponseFailed
     */
    public function getRoute(array $coordinates, OpenRailRoutingProfile $profile = OpenRailRoutingProfile::ALL_TRACKS): RouteDto
    {
        $gpxFile = new GpxFile();
        $track = new Track();
        $segment = new Segment();
        $segment->points = $coordinates;
        $track->segments[] = $segment;
        $gpxFile->tracks[] = $track;

        $url = sprintf(
            '%s/match?type=json&key=&elevation=false&instructions=false&profile=%s',
            self::API_URL,
            $profile->value,
        );
        $response = $this->getClient()->post($url, [
            'headers' => ['Content-Type' => 'application/gpx+xml'],
            'body' => $gpxFile->toXML()->saveXML(),
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new OpenRailRoutingResponseFailed('Failed to get route from BRouter: ' . $response->getBody());
        }

        $json = json_decode($response->getBody()->getContents(), true);
        if (isset($json['error'])) {
            throw new OpenRailRoutingResponseFailed('BRouter returned an error: ' . $json['error']);
        }
        if (empty($json['paths'])) {
            throw new OpenRailRoutingResponseFailed('BRouter returned no route features.');
        }

        $data = $json['paths'][0];
        $properties = ['distance' => $data['distance'], 'time' => $data['time'] / 1000];

        if ($data['points_encoded']) {
            $transcoder = new PolylineTranscoder();
            $points = $transcoder->decodePolyline($data['points'], (int) log10((float) $data['points_encoded_multiplier']));

            $coordinates = [];
            foreach ($points as $point) {
                $coordinates[] = new Coordinate($point->getLatitude(), $point->getLongitude());
            }

            return new RouteDto(
                new Feature(coordinates: $coordinates, type: 'LineString', properties: $properties),
                $data['distance'],
                $data['time'] / 1000
            );
        }

        if (!isset($data['points']['coordinates'])) {
            throw new OpenRailRoutingResponseFailed('BRouter returned no route coordinates.');
        }

        $coordinates = [];
        foreach ($data['points']['coordinates'] as $point) {
            $coordinates[] = new Coordinate($point[1], $point[0]);
        }

        return new RouteDto(
            new Feature(coordinates: $coordinates, type: 'LineString', properties: $properties),
            $data['distance'],
            $data['time'] / 1000
        );
    }
}
