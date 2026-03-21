<?php

namespace App\Repositories;

use App\Enum\SegmentPathType;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\StationIdentifier;
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
        ?SegmentPathType $pathType = null,
        ?string $excludeId = null,
    ): ?RouteSegment {
        // Use ±10% tolerance, but at least ±5 minutes for short-distance segments
        $tolerance = max(300, (int) round($duration * 0.1));
        $durationRange = [max(0, $duration - $tolerance), $duration + $tolerance];

        $fromIdentifierId = $start->station_identifier_id;
        $toIdentifierId = $end->station_identifier_id;

        // When both stopovers have identifiers, prefer an exact identifier-based segment.
        if ($fromIdentifierId !== null && $toIdentifierId !== null) {
            $segment = RouteSegment::where('from_identifier_id', $fromIdentifierId)
                ->where('to_identifier_id', $toIdentifierId)
                ->where(fn ($q) => $q->where('path_type', $pathType)->orWhereNull('path_type'))
                ->whereBetween('duration', $durationRange)
                ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
                ->first();

            if ($segment !== null) {
                return $segment;
            }
        }

        // Fall back to station-based lookup (only segments without identifier specificity).
        return RouteSegment::where('from_station_id', $start->train_station_id)
            ->where('to_station_id', $end->train_station_id)
            ->whereNull('from_identifier_id')
            ->whereNull('to_identifier_id')
            ->where(fn ($q) => $q->where('path_type', $pathType)->orWhereNull('path_type'))
            ->whereBetween('duration', $durationRange)
            ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
            ->first();
    }

    public function createRouteSegment(
        Station $fromStation,
        Station $toStation,
        string $encodedPolyline,
        int $polylinePrecision = 5,
        ?int $duration = null,
        ?SegmentPathType $pathType = null,
        ?int $distanceInMeters = null,
        ?StationIdentifier $fromIdentifier = null,
        ?StationIdentifier $toIdentifier = null,
    ): RouteSegment {
        $segment = new RouteSegment();
        $segment->from_station_id = $fromStation->id;
        $segment->from_identifier_id = $fromIdentifier?->id;
        $segment->to_station_id = $toStation->id;
        $segment->to_identifier_id = $toIdentifier?->id;
        $segment->duration = $duration;
        $segment->distance = $distanceInMeters;
        $segment->path_type = $pathType;
        $segment->polyline = $encodedPolyline;
        $segment->polyline_precision = $polylinePrecision;

        $segment->save();

        return $segment;
    }
}
