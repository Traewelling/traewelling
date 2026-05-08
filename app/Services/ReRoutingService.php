<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Coordinate;
use App\Enum\SegmentPathType;
use App\Exceptions\BRouterException;
use App\Jobs\RecalculateStatusesDistanceForTrip;
use App\Models\Stopover;
use App\Models\Trip;
use App\Repositories\TripRepository;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use phpGPX\Helpers\DistanceCalculator;
use Traewelling\GooglePolyline\PolylineTranscoder;

class ReRoutingService
{
    private int $stopovers = 1;

    private int $queryExceptions = 0;

    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly BRouterService $brouterService,
        private readonly GeodesicService $geodesicService,
    ) {}

    public function rerouteStops(Trip $trip): int
    {
        Log::info('RerouteStops: Starting rerouting process for trip', ['trip_id' => $trip->id]);
        /** @var Collection<int, Stopover> $stops */
        $stops = $trip->stopovers()->get();
        $this->stopovers = $stops->count();

        $count = 0;
        foreach ($stops as $key => $stop) {
            $previousStop = $stops[$key - 1] ?? null;
            if (!$previousStop) {
                continue;
            }
            if ($previousStop->routeSegment !== null) {
                Log::debug('RerouteStops: Segment already assigned to this stopover pair, skipping', ['stop_id' => $previousStop->id]);
                $this->stopovers--;

                continue;
            }
            $count++;

            $mode = $trip->category;

            $pathType = $mode->getSegmentPathType();
            if (!$pathType) {
                Log::warning('RerouteStops: Unsupported transport mode, skipping', ['mode' => $mode]);

                continue;
            }
            Log::debug('RerouteStops: Transport mode', ['mode' => $mode, 'pathType' => $pathType]);

            if ($pathType === SegmentPathType::GREAT_CIRCLE) {
                $this->rerouteAsGreatCircle($previousStop, $stop);
            } else {
                $this->rerouteBetween($previousStop, $stop, $pathType);
            }
        }

        if ($count === 0) {
            Log::error('RerouteStops: No stopovers found for trip', ['trip_id' => $trip->id]);
        }

        if ($this->stopovers < 1) {
            $errorPercentage = $this->queryExceptions;
        } else {
            $errorPercentage = $this->queryExceptions / ($this->stopovers) * 100;
        }

        if ($errorPercentage < 10) {
            RecalculateStatusesDistanceForTrip::dispatch($trip->trip_id);
        }

        return (int) $errorPercentage;
    }

    private function getDeviationThreshold(int|float $oldDistance): array
    {
        $percentage = config('trwl.distance_deviation.threshold_percent') / 100;
        if ($oldDistance == 0) {
            return [0, PHP_INT_MAX, 1.0];
        } elseif ($oldDistance < config('trwl.distance_deviation.shortest_distance')) {
            // for distances < 400 m, allow the highest deviation
            $percentage = config('trwl.distance_deviation.threshold_percent_shortest') / 100;
        } elseif ($oldDistance < config('trwl.distance_deviation.short_distance')) {
            // for distances < 1 km, allow a higher deviation
            $percentage = config('trwl.distance_deviation.threshold_percent_short') / 100;
        } elseif ($oldDistance < config('trwl.distance_deviation.medium_distance')) {
            // for distances < 10 km, allow a medium deviation
            $percentage = config('trwl.distance_deviation.threshold_percent_medium') / 100;
        }

        $upperLimit = $oldDistance * (1 + $percentage);
        $lowerLimit = $oldDistance * (1 - $percentage);

        return [$lowerLimit, $upperLimit, $percentage];
    }

    private function rerouteAsGreatCircle(Stopover $start, Stopover $end): void
    {
        $startTime = $start->departure_planned ?? $start->arrival_planned;
        $endTime = $end->arrival_planned ?? $end->departure_planned;
        $startLocation = $start->stationIdentifier?->location ?? $start->station?->location;
        $endLocation = $end->stationIdentifier?->location ?? $end->station?->location;

        if (!$startLocation || !$endLocation) {
            Log::warning('RerouteStops: Missing station location, cannot create great-circle arc', [
                'from_station_id' => $start->station?->id,
                'to_station_id' => $end->station?->id,
            ]);

            return;
        }

        $duration = -1;
        if ($startTime?->isValid() && $endTime?->isValid()) {
            $duration = (int) round($startTime->diffInSeconds($endTime));
        }

        $segment = $this->tripRepository->getRouteSegmentBetweenStops($start, $end, $duration, SegmentPathType::GREAT_CIRCLE);
        if ($segment) {
            Log::debug('RerouteStops: Great-circle segment already exists, reusing', ['segment' => $segment->id]);
            $this->tripRepository->setRouteSegmentForStop($start, $segment);

            return;
        }

        $startCoord = new Coordinate($startLocation->latitude, $startLocation->longitude);
        $endCoord = new Coordinate($endLocation->latitude, $endLocation->longitude);

        $coordinates = $this->geodesicService->interpolate($startCoord, $endCoord);
        $distanceInMeters = $this->geodesicService->haversineDistance($startCoord, $endCoord);

        $encodedPolyline = new PolylineTranscoder()->encodePolyline(
            array_map(static fn (Coordinate $c) => [$c->longitude, $c->latitude], $coordinates),
        );

        $segment = $this->tripRepository->createRouteSegment(
            fromStation: $start->station,
            toStation: $end->station,
            encodedPolyline: $encodedPolyline,
            duration: $duration,
            pathType: SegmentPathType::GREAT_CIRCLE,
            distanceInMeters: $distanceInMeters,
            fromIdentifier: $start->stationIdentifier,
            toIdentifier: $end->stationIdentifier,
        );
        $this->tripRepository->setRouteSegmentForStop($start, $segment);

        Log::debug('RerouteStops: Great-circle arc created', [
            'segment_id' => $segment->id,
            'distance_m' => $distanceInMeters,
            'from' => $start->station?->name,
            'to' => $end->station?->name,
        ]);
    }

    private function rerouteBetween(Stopover $start, Stopover $end, SegmentPathType $pathType): void
    {
        Log::debug('RerouteStops', [$start, $end, $pathType]);

        $startTime = $start->departure_planned ?? $start->arrival_planned;
        $endTime = $end->arrival_planned ?? $end->departure_planned;
        $startLocation = $start->stationIdentifier?->location ?? $start->station?->location;
        $endLocation = $end->stationIdentifier?->location ?? $end->station?->location;
        if (!$startLocation || !$endLocation) {
            Log::warning('RerouteStops: Missing station location, cannot reroute', [
                'from_station_id' => $start->station?->id,
                'to_station_id' => $end->station?->id,
            ]);

            return;
        }

        $oldDistance = (new DistanceCalculator([$startLocation, $endLocation]))->getRealDistance();

        $duration = -1;
        if ($startTime?->isValid() && $endTime?->isValid()) {
            $duration = (int) round($startTime->diffInSeconds($endTime));
            Log::debug('RerouteStops', [$duration, $pathType]);
        }

        $segment = $this->tripRepository->getRouteSegmentBetweenStops($start, $end, $duration, $pathType);
        if ($segment) {
            Log::debug('RerouteStops: Segment already exists, setting for stop', ['segment' => $segment->id]);
            $this->tripRepository->setRouteSegmentForStop($start, $segment);

            return;
        }

        $originCoord = new Coordinate($startLocation->latitude, $startLocation->longitude);
        $destCoord = new Coordinate($endLocation->latitude, $endLocation->longitude);
        $hadInfraError = false;
        $noTrackFound = false;

        $route = $this->tryBrouterRoute($originCoord, $destCoord, $duration, $oldDistance, $pathType, $start->station->name, $end->station->name, false, $hadInfraError, $noTrackFound);

        if ($route === null && !$noTrackFound) {
            // Phase 1: jitter origin up to 2x
            for ($i = 0; $i < 2 && $route === null; $i++) {
                $route = $this->tryBrouterRoute($this->jitterCoordinate($originCoord), $destCoord, $duration, $oldDistance, $pathType, $start->station->name, $end->station->name, true, $hadInfraError, $noTrackFound);
            }
        }

        if ($route === null && !$noTrackFound) {
            // Phase 2: jitter destination up to 2x
            for ($i = 0; $i < 2 && $route === null; $i++) {
                $route = $this->tryBrouterRoute($originCoord, $this->jitterCoordinate($destCoord), $duration, $oldDistance, $pathType, $start->station->name, $end->station->name, true, $hadInfraError, $noTrackFound);
            }
        }

        if ($route === null && !$noTrackFound) {
            // Phase 3: jitter both simultaneously up to 2x
            for ($i = 0; $i < 2 && $route === null; $i++) {
                $route = $this->tryBrouterRoute($this->jitterCoordinate($originCoord), $this->jitterCoordinate($destCoord), $duration, $oldDistance, $pathType, $start->station->name, $end->station->name, true, $hadInfraError, $noTrackFound);
            }
        }

        if ($route === null) {
            if ($hadInfraError) {
                $this->queryExceptions++;
            }
            Log::debug('RerouteStops: All jitter attempts failed, skipping segment', [
                'from' => $start->station->name,
                'to' => $end->station->name,
            ]);

            return;
        }

        $segment = $this->tripRepository->createRouteSegment(
            fromStation: $start->station,
            toStation: $end->station,
            encodedPolyline: $route['polyline'],
            duration: $duration,
            pathType: $pathType,
            distanceInMeters: $route['distance'],
            fromIdentifier: $start->stationIdentifier,
            toIdentifier: $end->stationIdentifier,
        );
        $this->tripRepository->setRouteSegmentForStop($start, $segment);

        if ($route['jittered']) {
            Log::debug('RerouteStops: Segment created after coordinate jitter', [
                'from' => $start->station->name,
                'to' => $end->station->name,
                'distance_m' => $route['distance'],
            ]);
        }
    }

    /**
     * @return array{polyline: string, distance: int, jittered: bool}|null
     */
    private function tryBrouterRoute(
        Coordinate $fromCoord,
        Coordinate $toCoord,
        int $duration,
        float $oldDistance,
        SegmentPathType $pathType,
        string $fromName,
        string $toName,
        bool $jittered = false,
        bool &$hadInfraError = false,
        bool &$noTrackFound = false,
    ): ?array {
        try {
            $route = $this->brouterService->getRoute([$fromCoord, $toCoord], $pathType->getBRouterProfile());

            if ($duration > 0) {
                $speed = ($route->distanceInMeters / $duration) * 3.6;
                if ($speed > 300) {
                    Log::warning('RerouteStops: Calculated speed is too high, skipping route segment', [
                        'speed_kmh' => $speed,
                        'from' => $fromName,
                        'to' => $toName,
                        'jittered' => $jittered,
                    ]);

                    return null;
                }
            } elseif ($route->distanceInMeters > 1000) {
                Log::warning('RerouteStops: No duration available and distance is too high, skipping route segment', [
                    'distance_m' => $route->distanceInMeters,
                    'from' => $fromName,
                    'to' => $toName,
                ]);

                return null;
            }

            [$lowerLimit, $upperLimit, $percentage] = $this->getDeviationThreshold($oldDistance);

            if ($oldDistance != 0 && ($route->distanceInMeters > $upperLimit || $route->distanceInMeters < $lowerLimit)) {
                Log::debug(
                    sprintf('Distance deviation is greater than %d percent.', $percentage * 100),
                    [
                        'from' => $fromName,
                        'to' => $toName,
                        'old_distance_m' => $oldDistance,
                        'new_distance_m' => $route->distanceInMeters,
                        'upper_limit_m' => $upperLimit,
                        'lower_limit_m' => $lowerLimit,
                        'jittered' => $jittered,
                    ]
                );

                return null;
            }

            $encodedPolyline = new PolylineTranscoder()->encodePolyline(
                array_map(static fn (Coordinate $c) => [$c->longitude, $c->latitude], $route->coordinates),
            );

            return ['polyline' => $encodedPolyline, 'distance' => $route->distanceInMeters, 'jittered' => $jittered];
        } catch (BRouterException|GuzzleException $e) {
            if ($e instanceof ClientException) {
                Log::warning('RerouteStops: ClientException details', [
                    'response' => $e->getResponse()?->getBody()->getContents(),
                    'request' => $e->getRequest()?->getBody()->getContents(),
                ]);

                return null; // client errors are not infra errors
            }
            $hadInfraError = true;
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                return null;
            }
            if (str_contains($e->getMessage(), 'no track found')) {
                $noTrackFound = true;
                Log::debug('RerouteStops: BRouter found no track, skipping segment', [
                    'from' => $fromName,
                    'to' => $toName,
                    'jittered' => $jittered,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
            if (str_contains($e->getMessage(), 'from requested start') || str_contains($e->getMessage(), 'from requested end')) {
                Log::debug('RerouteStops: BRouter route endpoint too far from waypoint, skipping segment', [
                    'from' => $fromName,
                    'to' => $toName,
                    'jittered' => $jittered,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
            Log::error('RerouteStops: Failed to create route segment', ['error' => $e->getMessage()]);
            report($e);

            return null;
        } catch (\Exception $e) {
            Log::error('RerouteStops: Failed to create route segment', ['error' => $e->getMessage()]);
            report($e);

            return null; // generic exceptions are not counted as infra errors
        }
    }

    private function jitterCoordinate(Coordinate $coord, float $meters = 20.0): Coordinate
    {
        $latDeg = $meters / 111_320;
        $lonDeg = $meters / (111_320 * cos(deg2rad($coord->latitude)));

        $angle = lcg_value() * 2 * M_PI;
        $r = sqrt(lcg_value());

        return new Coordinate(
            $coord->latitude + $r * cos($angle) * $latDeg,
            $coord->longitude + $r * sin($angle) * $lonDeg,
        );
    }
}
