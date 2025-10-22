<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenRailRoutingResponseFailed;
use App\Models\Stopover;
use App\Models\Trip;
use App\OpenRailRoutingProfile;
use App\Repositories\TripRepository;
use App\Services\OpenRailRoutingService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Traewelling\GooglePolyline\PolylineTranscoder;

class ReRoutingController extends Controller
{
    private TripRepository $tripRepository;
    private OpenRailRoutingService $openRailRoutingService;

    public function __construct(
        TripRepository $tripRepository,
        OpenRailRoutingService $openRailRoutingService
    ) {
        $this->tripRepository = $tripRepository;
        $this->openRailRoutingService = $openRailRoutingService;
    }

    public function rerouteStops(Trip $tripDto): void {
        /** @var Collection<int, Stopover> $stops */
        $stops = $tripDto->stopovers()->get();

        $count = 0;
        foreach ($stops as $key => $stop) {
            $previousStop = $stops[$key - 1] ?? null;
            if (!$previousStop) {
                continue;
            }
            $count++;

            $mode = $tripDto->category;

            $pathType = $mode->getORRProfile();
            if (!$pathType) {
                Log::warning('RerouteStops: Unsupported transport mode, skipping', ['mode' => $mode]);

                continue;
            }
            Log::debug('RerouteStops: Transport mode', ['mode' => $mode, 'pathType' => $pathType]);
            $this->rerouteBetween($previousStop, $stop, $pathType);
        }

        if ($count === 0) {
            Log::error('RerouteStops: No stopovers found for trip', ['trip_id' => $tripDto->id]);
        }
    }

    private function rerouteBetween(Stopover $start, Stopover $end, OpenRailRoutingProfile $pathType): void
    {
        Log::debug('RerouteStops', [$start, $end, $pathType]);

        $startTime = $start->departure ?? $start->arrival;
        $endTime = $end->arrival ?? $end->departure;

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
                'to' => $end->station,
                'type' => $pathType,
            ]);
            $route = $this->openRailRoutingService->getRoute([$start->station->location, $end->station->location], $pathType);
        } catch (OpenRailRoutingResponseFailed|GuzzleException $e) {
            report($e);
            $route = null;
        }

        try {
            $encodedPolyline = (new PolylineTranscoder)->encodePolyline($route->feature->getCoordinateArray());

            // if speed is > 300 km/h, we assume the route is invalid
            if ($duration > 0) {
                $speed = ($route->distanceInMeters / $duration) * 3.6; // m/s to km/h
                if ($speed > 300) {
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
    }
}
