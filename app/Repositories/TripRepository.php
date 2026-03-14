<?php

namespace App\Repositories;

use App\Enum\OpenRailRoutingProfile;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\Stopover;

class TripRepository
{
    public function setRouteSegmentForStop(
        Stopover $stop,
        RouteSegment $routeSegment
    ): void {
        $stop->route_segment_id = $routeSegment->id;
        $stop->save();
    }

    public function getRouteSegmentBetweenStops(
        Stopover $start,
        Stopover $end,
        int $duration,
        ?OpenRailRoutingProfile $pathType = null,
        ?string $excludeId = null,
    ): ?RouteSegment {
        // Use ±10% tolerance, but at least ±5 minutes for short-distance segments
        $tolerance = max(300, (int) round($duration * 0.1));

        return RouteSegment::where('from_station_id', $start->train_station_id)
            ->where('to_station_id', $end->train_station_id)
            ->where(fn ($q) => $q->where('path_type', $pathType)->orWhereNull('path_type'))
            ->whereBetween('duration', [max(0, $duration - $tolerance), $duration + $tolerance])
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->first();
    }

    public function createRouteSegment(
        Station $fromStation,
        Station $toStation,
        string $encodedPolyline,
        int $polylinePrecision = 5,
        ?int $duration = null,
        ?OpenRailRoutingProfile $pathType = null,
        ?int $distanceInMeters = null,
    ): RouteSegment {
        $segment = new RouteSegment();
        $segment->from_station_id = $fromStation->id;
        $segment->to_station_id = $toStation->id;
        $segment->duration = $duration;
        $segment->distance = $distanceInMeters;
        $segment->path_type = $pathType;
        $segment->polyline = $encodedPolyline;
        $segment->polyline_precision = $polylinePrecision;

        $segment->save();

        return $segment;
    }
}
