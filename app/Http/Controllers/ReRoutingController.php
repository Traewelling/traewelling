<?php

namespace App\Http\Controllers;

use App\Enum\OpenRailRoutingProfile;
use App\Exceptions\OpenRailRoutingResponseFailed;
use App\Jobs\RecalculateStatusesDistanceForTrip;
use App\Models\Stopover;
use App\Models\Trip;
use App\Repositories\TripRepository;
use App\Services\OpenRailRoutingService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use phpGPX\Helpers\DistanceCalculator;
use Traewelling\GooglePolyline\PolylineTranscoder;

class ReRoutingController extends Controller
{
    private TripRepository $tripRepository;

    private OpenRailRoutingService $openRailRoutingService;

    private int $stopovers = 1;

    private int $queryExceptions = 0;

    public function __construct(
        TripRepository $tripRepository,
        OpenRailRoutingService $openRailRoutingService
    ) {
        $this->tripRepository = $tripRepository;
        $this->openRailRoutingService = $openRailRoutingService;
    }

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
            RecalculateStatusesDistanceForTrip::dispatch($trip->trip_id);
        }

        return $errorPercentage;
    }

    private function getDeviationThreshold(int $oldDistance): array
    {
        $percentage = config('trwl.distance_deviation.threshold_percent') / 100;
        if ($oldDistance === 0) {
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

    private function rerouteBetween(Stopover $start, Stopover $end, OpenRailRoutingProfile $pathType): void
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

            return; // already rerouted
        }
        Log::debug('Getting new route from OpenRailwayRouting', [
            'from' => $start->station,
            'to' => $end->station,
            'type' => $pathType,
        ]);

        try {
            $route = $this->openRailRoutingService->getRoute([$startLocation, $endLocation], $pathType);
            $encodedPolyline = (new PolylineTranscoder())->encodePolyline($route->feature->getCoordinateArray());

            // if speed is > 300 km/h, we assume the route is invalid
            if ($duration > 0) {
                $speed = ($route->distanceInMeters / $duration) * 3.6; // m/s to km/h
                if ($speed > 300) { // TODO: make configurable per transport mode
                    Log::warning('RerouteStops: Calculated speed is too high, skipping route segment', [
                        'speed_kmh' => $speed,
                        'from' => $start->station->name,
                        'to' => $end->station->name,
                    ]);

                    return;
                }
            } elseif ($route->distanceInMeters > 1000) {
                Log::warning('RerouteStops: No duration available and distance is too high, skipping route segment', [
                    'distance_m' => $route->distanceInMeters,
                    'from' => $start->station->name,
                    'to' => $end->station->name,
                ]);

                return;
            }

            $distance = $route->distanceInMeters;
            [$lowerLimit, $upperLimit, $percentage] = $this->getDeviationThreshold($oldDistance);

            if (($oldDistance !== 0 && ($distance > $upperLimit || $distance < $lowerLimit))) {
                Log::debug(
                    sprintf('Distance deviation is greater than %d percent.', $percentage * 100),
                    [
                        'from' => $start->station->name,
                        'to' => $end->station->name,
                        'old_distance_m' => $oldDistance,
                        'new_distance_m' => $distance,
                        'upper_limit_m' => $upperLimit,
                        'lower_limit_m' => $lowerLimit,
                    ]
                );

                return;
            }

            // TODO: path_type is temporarily set to null because the existing OpenRailRoutingProfile
            //       values (e.g. tgv_all, all_tracks) are not meaningful for our use.
            //       Introduce a proper categorisation (e.g. rail, street, water, air) and
            //       re-enable path_type assignment here once that is in place.
            $segment = $this->tripRepository->createRouteSegment(
                fromStation: $start->station,
                toStation: $end->station,
                encodedPolyline: $encodedPolyline,
                duration: $duration,
                pathType: null,
                distanceInMeters: $route->distanceInMeters
            );
            $this->tripRepository->setRouteSegmentForStop($start, $segment);
        } catch (OpenRailRoutingResponseFailed|GuzzleException $e) {
            Log::error('RerouteStops: Failed to create route segment', ['error' => $e->getMessage()]);
            if ($e instanceof ClientException) {
                Log::warning('RerouteStops: ClientException details', [
                    'response' => $e->getResponse()?->getBody()->getContents(),
                    'request' => $e->getRequest()?->getBody()->getContents(),
                ]);

                return;
            }
            $this->queryExceptions++;
            if (str_contains($e->getMessage(), 'cURL error 28')) {
                return;
            }
            report($e);
        } catch (\Exception $e) {
            Log::error('RerouteStops: Failed to create route segment', ['error' => $e->getMessage()]);
            report($e);
        }

    }
}
