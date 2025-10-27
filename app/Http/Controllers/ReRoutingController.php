<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenRailRoutingResponseFailed;
use App\Jobs\RecalculateStatusesDistanceForTrip;
use App\Models\Stopover;
use App\Models\Trip;
use App\OpenRailRoutingProfile;
use App\Repositories\TripRepository;
use App\Services\OpenRailRoutingService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use phpGPX\Helpers\DistanceCalculator;
use Traewelling\GooglePolyline\PolylineTranscoder;

class ReRoutingController extends Controller
{
    private TripRepository         $tripRepository;
    private OpenRailRoutingService $openRailRoutingService;
    private int                    $stopovers       = 1;
    private int                    $queryExceptions = 0;

    public function __construct(
        TripRepository         $tripRepository,
        OpenRailRoutingService $openRailRoutingService
    ) {
        $this->tripRepository         = $tripRepository;
        $this->openRailRoutingService = $openRailRoutingService;
    }

    public function rerouteStops(Trip $trip): int {
        /** @var Collection<int, Stopover> $stops */
        $stops           = $trip->stopovers()->get();
        $this->stopovers = $stops->count();

        $count = 0;
        foreach ($stops as $key => $stop) {
            $previousStop = $stops[$key - 1] ?? null;
            if (!$previousStop) {
                continue;
            }
            if ($stop->routeSegment !== null) {
                Log::debug('RerouteStops: At least one stop already has route segment, skipping whole process', ['stop_id' => $stop->id]);
                $this->stopovers--;

                continue;
            }
            $count++;

            $mode = $trip->category;

            $pathType = $mode->getORRProfile();
            if (!$pathType) {
                Log::warning('RerouteStops: Unsupported transport mode, skipping', ['mode' => $mode]);

                continue;
            }
            Log::debug('RerouteStops: Transport mode', ['mode' => $mode, 'pathType' => $pathType]);
            $this->rerouteBetween($previousStop, $stop, $pathType);
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
            RecalculateStatusesDistanceForTrip::dispatch($trip->id);
        }

        return $errorPercentage;
    }

    private function rerouteBetween(Stopover $start, Stopover $end, OpenRailRoutingProfile $pathType): void {
        Log::debug('RerouteStops', [$start, $end, $pathType]);

        $startTime     = $start->departure ?? $start->arrival;
        $endTime       = $end->arrival ?? $end->departure;
        $startLocation = $start->stationIdentifier?->location ?? $start->station?->location;
        $endLocation   = $end->stationIdentifier?->location ?? $end->station?->location;
        if (!$startLocation || !$endLocation) {
            Log::warning('RerouteStops: Missing station location, cannot reroute', [
                'from_station_id' => $start->station?->id,
                'to_station_id'   => $end->station?->id,
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

            return; // already rerouted
        }
        try {
            Log::debug('Getting new route from OpenRailwayRouting', [
                'from' => $start->station,
                'to'   => $end->station,
                'type' => $pathType,
            ]);

            try {
                $route           = $this->openRailRoutingService->getRoute([$startLocation, $endLocation], $pathType);
                $encodedPolyline = (new PolylineTranscoder)->encodePolyline($route->feature->getCoordinateArray());

                // if speed is > 300 km/h, we assume the route is invalid
                if ($duration > 0) {
                    $speed = ($route->distanceInMeters / $duration) * 3.6; // m/s to km/h
                    if ($speed > 300) { //TODO: make configurable per transport mode
                        Log::warning('RerouteStops: Calculated speed is too high, skipping route segment', [
                            'speed_kmh' => $speed,
                            'from'      => $start->station->name,
                            'to'        => $end->station->name,
                        ]);
                        return;
                    }
                } elseif ($route->distanceInMeters > 1000) {
                    Log::warning('RerouteStops: No duration available and distance is too high, skipping route segment', [
                        'distance_m' => $route->distanceInMeters,
                        'from'       => $start->station->name,
                        'to'         => $end->station->name,
                    ]);
                    return;
                }

                $percentage = config('trwl.distance_deviation_threshold_percent', 15) / 100;
                $upperLimit = $oldDistance * (1 + $percentage);
                $lowerLimit = $oldDistance * (1 - $percentage);
                $distance   = $route->distanceInMeters;

                if ($distance === 0 || ($oldDistance !== 0 && ($distance > $upperLimit || $distance < $lowerLimit))) {
                    Log::warning(
                        sprintf('Distance deviation is greater than %d percent.', $percentage * 100),
                        [
                            'from'           => $start->station->name,
                            'to'             => $end->station->name,
                            'old_distance_m' => $oldDistance,
                            'new_distance_m' => $distance,
                            'upper_limit_m'  => $upperLimit,
                            'lower_limit_m'  => $lowerLimit,
                        ]
                    );
                    return;
                }

                $segment = $this->tripRepository->createRouteSegment(
                    fromStation:      $start->station,
                    toStation:        $end->station,
                    encodedPolyline:  $encodedPolyline,
                    duration:         $duration,
                    pathType:         $pathType,
                    distanceInMeters: $route->distanceInMeters
                );
                $this->tripRepository->setRouteSegmentForStop($start, $segment);
            } catch (\Exception $e) {
                Log::error('RerouteStops: Failed to create route segment', ['error' => $e->getMessage()]);
                report($e);
            }
        } catch (OpenRailRoutingResponseFailed|GuzzleException $e) {
            $this->queryExceptions++;
            report($e);
        }
    }
}
