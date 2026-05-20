<?php

namespace App\Repositories;

use App\DataProviders\DataProviderBuilder;
use App\Enum\SegmentPathType;
use App\Exceptions\DataProviderException;
use App\Models\RouteSegment;
use App\Models\Station;
use App\Models\StationIdentifier;
use App\Models\Stopover;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;
use JsonException;

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

        // When both stopovers have identifiers, require an identifier-based segment.
        // There is no fallback to station-to-station, if no match exists, return null
        // so the caller can create a new identifier-to-identifier segment.
        if ($fromIdentifierId !== null && $toIdentifierId !== null) {
            return RouteSegment::where('from_identifier_id', $fromIdentifierId)
                ->where('to_identifier_id', $toIdentifierId)
                ->where(fn ($q) => $q->where('path_type', $pathType)->orWhereNull('path_type'))
                ->whereBetween('duration', $durationRange)
                ->when($excludeId !== null, fn ($q) => $q->where('id', '!=', $excludeId))
                ->first();
        }

        // Fallback for stopovers without an identifier assigned (should not occur normally).
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

    /**
     * @throws DataProviderException
     * @throws JsonException
     */
    public function getByIdentifier(string $tripID, string $lineName): Trip
    {
        // todo: create trip IDs with a prefix, to distinguish between different data providers
        $dataProvider = new DataProviderBuilder()->build(null, Auth::user());

        if (is_numeric($tripID)) {
            $trip = Trip::where('id', $tripID)->where('linename', $lineName)->first();

            return $trip ?? $dataProvider->fetchHafasTrip($tripID, $lineName);
        }
        $trip = Trip::where('trip_id', $tripID)->first();

        if ($trip === null) {
            return $dataProvider->fetchHafasTrip($tripID, $lineName);
        }

        if ($trip->linename === $lineName) {
            return $trip;
        }

        if ($trip->continuation_trip_id !== null) {
            $match = $this->findInContinuationChainByLineName($trip, $lineName);
            if ($match !== null) {
                return $match;
            }
        }

        $trip = $dataProvider->fetchHafasTrip($tripID, $lineName);

        if ($trip->linename === $lineName) {
            return $trip;
        }

        if ($trip->continuation_trip_id !== null) {
            $match = $this->findInContinuationChainByLineName($trip, $lineName);
            if ($match !== null) {
                return $match;
            }
        }

        return $trip;
    }

    private function findInContinuationChainByLineName(Trip $trip, string $lineName): ?Trip
    {
        $seenIds = [$trip->id];
        $continuationId = $trip->continuation_trip_id;

        while ($continuationId !== null) {
            if (in_array($continuationId, $seenIds, true)) {
                break;
            }
            $continuation = Trip::find($continuationId);
            if ($continuation === null) {
                break;
            }
            if ($continuation->linename === $lineName) {
                return $continuation;
            }
            $seenIds[] = $continuation->id;
            $continuationId = $continuation->continuation_trip_id;
        }

        return null;
    }
}
