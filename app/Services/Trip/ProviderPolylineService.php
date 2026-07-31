<?php

declare(strict_types=1);

namespace App\Services\Trip;

use App\DataProviders\DataProviderBuilder;
use App\Dto\Coordinate;
use App\Dto\Internal\ProviderPolylineImportResult;
use App\Enum\SegmentPathType;
use App\Enum\TripSource;
use App\Exceptions\DataProviderException;
use App\Models\Stopover;
use App\Models\Trip;
use App\Repositories\TripRepository;
use App\Services\GeodesicService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Traewelling\GooglePolyline\PolylineTranscoder;

class ProviderPolylineService
{
    private const int MAX_SNAP_DISTANCE_METERS = 1_000;

    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly GeodesicService $geodesicService,
    ) {}

    public function importForTrip(Trip $trip): ProviderPolylineImportResult
    {
        Log::debug('ProviderPolyline: Starting import for trip', [
            'trip_id' => $trip->id,
            'provider_trip_id' => $trip->trip_id,
            'linename' => $trip->linename,
            'journey_number' => $trip->journey_number,
            'category' => $trip->category?->value,
            'source' => $trip->source?->value,
            'motis_source' => $trip->motis_source,
            'origin' => $trip->originStation?->name,
            'destination' => $trip->destinationStation?->name,
            'departure' => $trip->departure?->toIso8601String(),
            'arrival' => $trip->arrival?->toIso8601String(),
        ]);

        if ($trip->source !== TripSource::TRANSITOUS) {
            Log::debug('ProviderPolyline: Skipping, trip source ships no geometry', [
                'trip_id' => $trip->id,
                'source' => $trip->source?->value,
            ]);

            return ProviderPolylineImportResult::aborted('trip is not from a geometry-carrying provider');
        }

        $pathType = $trip->category?->getSegmentPathType();
        if ($pathType === null) {
            Log::debug('ProviderPolyline: Skipping, transport mode maps to no path type', [
                'trip_id' => $trip->id,
                'category' => $trip->category?->value,
            ]);

            return ProviderPolylineImportResult::aborted('transport mode has no segment path type');
        }

        $stopovers = $trip->stopovers()->with(['station', 'stationIdentifier'])->get();
        if ($stopovers->count() < 2) {
            Log::debug('ProviderPolyline: Skipping, not enough stopovers to form a segment', [
                'trip_id' => $trip->id,
                'stopovers' => $stopovers->count(),
            ]);

            return ProviderPolylineImportResult::aborted('trip has less than two stopovers');
        }

        $gaps = $stopovers->slice(0, -1)->filter(fn (Stopover $stopover) => $stopover->route_segment_id === null);
        Log::debug('ProviderPolyline: Checked existing segment coverage', [
            'trip_id' => $trip->id,
            'stopovers' => $stopovers->count(),
            'pairs' => $stopovers->count() - 1,
            'pairs_with_segment' => $stopovers->count() - 1 - $gaps->count(),
            'pairs_without_segment' => $gaps->count(),
            'path_type' => $pathType->value,
            'route' => $stopovers->map(fn (Stopover $stopover) => $stopover->station?->name)->implode(' -> '),
            'gaps_after' => $gaps->map(fn (Stopover $stopover) => $stopover->station?->name)->values()->all(),
        ]);

        if ($gaps->isEmpty()) {
            Log::debug('ProviderPolyline: Nothing to do, every pair already has a segment', ['trip_id' => $trip->id]);

            return new ProviderPolylineImportResult();
        }

        try {
            $geometry = $this->fetchGeometry($trip);
        } catch (DataProviderException $exception) {
            Log::warning('ProviderPolyline: Could not load journey from provider', [
                'trip_id' => $trip->id,
                'error' => $exception->getMessage(),
            ]);

            return ProviderPolylineImportResult::aborted('provider journey lookup failed');
        }

        if ($geometry === null) {
            Log::debug('ProviderPolyline: Provider delivered no usable geometry', ['trip_id' => $trip->id]);

            return ProviderPolylineImportResult::aborted('provider returned no usable geometry');
        }

        $indices = $this->snapStopoversToGeometry($stopovers, $geometry['coordinates']);
        if ($indices === null) {
            Log::debug('ProviderPolyline: Aborting, stopovers could not be mapped onto the geometry', [
                'trip_id' => $trip->id,
            ]);

            return ProviderPolylineImportResult::aborted('stopovers do not lie on the provider geometry');
        }

        $result = $this->createSegments($stopovers, $geometry['coordinates'], $indices, $geometry['precision'], $pathType);

        Log::debug('ProviderPolyline: Finished import for trip', [
            'trip_id' => $trip->id,
            'provider_trip_id' => $trip->trip_id,
            'linename' => $trip->linename,
            'pairs_without_segment_before' => $gaps->count(),
            ...$result->toArray(),
        ]);

        return $result;
    }

    /**
     * @param  Collection<int, Stopover>  $stopovers
     * @param  Coordinate[]  $coordinates
     * @param  int[]  $indices
     */
    private function createSegments(
        Collection $stopovers,
        array $coordinates,
        array $indices,
        int $precision,
        SegmentPathType $pathType,
    ): ProviderPolylineImportResult {
        $created = 0;
        $reused = 0;
        $skipped = 0;
        $createdDistance = 0;
        $createdPoints = 0;

        foreach ($stopovers as $key => $stopover) {
            $next = $stopovers[$key + 1] ?? null;
            if ($next === null) {
                break;
            }
            if ($stopover->route_segment_id !== null) {
                Log::debug('ProviderPolyline: Pair already has a segment, skipping', [
                    'from' => $stopover->station?->name,
                    'to' => $next->station?->name,
                    'pair_index' => $key,
                    'segment_id' => $stopover->route_segment_id,
                ]);

                continue;
            }
            if ($stopover->station === null || $next->station === null) {
                Log::debug('ProviderPolyline: Pair is missing a station, skipping', [
                    'pair_index' => $key,
                    'from_stopover_id' => $stopover->id,
                    'to_stopover_id' => $next->id,
                    'from_station_id' => $stopover->train_station_id,
                    'to_station_id' => $next->train_station_id,
                ]);
                $skipped++;

                continue;
            }

            $duration = $stopover->plannedSecondsUntil($next);
            $beeline = $this->beelineDistance($stopover, $next);

            $existing = $this->tripRepository->getRouteSegmentBetweenStops($stopover, $next, $duration, $pathType);
            if ($existing !== null) {
                $this->tripRepository->setRouteSegmentForStop($stopover, $existing);
                $reused++;

                Log::debug('ProviderPolyline: Reusing existing segment', [
                    'from' => $stopover->station->name,
                    'to' => $next->station->name,
                    'from_station_id' => $stopover->train_station_id,
                    'to_station_id' => $next->train_station_id,
                    'matched_by' => $stopover->station_identifier_id !== null && $next->station_identifier_id !== null ? 'identifier' : 'station',
                    'segment_id' => $existing->id,
                    'segment_distance_m' => $existing->distance,
                    'segment_duration_s' => $existing->duration,
                    'segment_path_type' => $existing->path_type,
                    'pair_duration_s' => $duration,
                    'duration_delta_s' => $existing->duration === null ? null : $duration - $existing->duration,
                    'beeline_m' => $beeline,
                    'detour_factor' => $beeline > 0 ? round($existing->distance / $beeline, 2) : null,
                ]);

                continue;
            }

            $slice = array_slice($coordinates, $indices[$key], $indices[$key + 1] - $indices[$key] + 1);
            if (count($slice) < 2) {
                Log::debug('ProviderPolyline: Pair collapses to a single geometry point, skipping', [
                    'from' => $stopover->station->name,
                    'to' => $next->station->name,
                    'pair_index' => $key,
                    'geometry_index' => $indices[$key],
                    'beeline_m' => $beeline,
                    'duration_s' => $duration,
                ]);
                $skipped++;

                continue;
            }

            $distance = (int) round($this->geodesicService->pathLength($slice));

            $segment = $this->tripRepository->createRouteSegment(
                fromStation: $stopover->station,
                toStation: $next->station,
                encodedPolyline: new PolylineTranscoder()->encodePolyline(
                    array_map(static fn (Coordinate $coordinate): array => $coordinate->toArray(), $slice),
                    $precision,
                ),
                polylinePrecision: $precision,
                duration: $duration,
                pathType: $pathType,
                distanceInMeters: $distance,
                fromIdentifier: $stopover->stationIdentifier,
                toIdentifier: $next->stationIdentifier,
            );
            $this->tripRepository->setRouteSegmentForStop($stopover, $segment);
            $created++;
            $createdDistance += $distance;
            $createdPoints += count($slice);

            Log::debug('ProviderPolyline: Created segment from provider geometry', [
                'from' => $stopover->station->name,
                'to' => $next->station->name,
                'from_station_id' => $stopover->train_station_id,
                'to_station_id' => $next->train_station_id,
                'from_identifier_id' => $stopover->station_identifier_id,
                'to_identifier_id' => $next->station_identifier_id,
                'segment_id' => $segment->id,
                'path_type' => $pathType->value,
                'points' => count($slice),
                'geometry_range' => $indices[$key] . '-' . $indices[$key + 1],
                'distance_m' => $distance,
                'beeline_m' => $beeline,
                // A detour factor far above ~1.5 or an implausible speed means the shape was cut wrong
                'detour_factor' => $beeline > 0 ? round($distance / $beeline, 2) : null,
                'duration_s' => $duration,
                'speed_kmh' => $duration > 0 ? round($distance / $duration * 3.6, 1) : null,
                'departure' => $stopover->departure_planned?->toIso8601String(),
                'arrival' => $next->arrival_planned?->toIso8601String(),
                'precision' => $precision,
                'encoded_bytes' => strlen($segment->polyline ?? ''),
            ]);
        }

        Log::debug('ProviderPolyline: Segment pass completed', [
            'created' => $created,
            'reused' => $reused,
            'skipped' => $skipped,
            'pairs_total' => $stopovers->count() - 1,
            'created_distance_km' => round($createdDistance / 1000, 2),
            'created_points' => $createdPoints,
            'path_type' => $pathType->value,
        ]);

        return new ProviderPolylineImportResult(created: $created, reused: $reused, skipped: $skipped);
    }

    /**
     * Map every stopover onto its closest point of the geometry.
     *
     * @param  Collection<int, Stopover>  $stopovers
     * @param  Coordinate[]  $coordinates
     * @return int[]|null null when a stopover is too far off the geometry to be trusted
     */
    private function snapStopoversToGeometry(Collection $stopovers, array $coordinates): ?array
    {
        $lastIndex = count($coordinates) - 1;
        $cursor = 0;
        $indices = [];
        $snapDistances = [];

        foreach ($stopovers as $stopover) {
            $location = $stopover->coordinate;
            if ($location === null) {
                Log::debug('ProviderPolyline: Stopover has no coordinates, cannot snap', [
                    'stopover_id' => $stopover->id,
                    'station' => $stopover->station?->name,
                ]);

                return null;
            }

            // Forward-only, so a line that returns to an earlier place cannot pull a stop backwards.
            $bestIndex = $this->geodesicService->findNearestPointIndex($location, $coordinates, $cursor);
            if ($bestIndex === null) {
                Log::debug('ProviderPolyline: Geometry ends before the remaining stopovers', [
                    'station' => $stopover->station?->name,
                    'searched_from_index' => $cursor,
                    'geometry_points' => $lastIndex + 1,
                    'snapped_before_failure' => count($indices),
                ]);

                return null;
            }

            $snapDistance = $this->geodesicService->haversineDistance($location, $coordinates[$bestIndex]);
            if ($snapDistance > self::MAX_SNAP_DISTANCE_METERS) {
                Log::debug('ProviderPolyline: Stopover too far away from geometry', [
                    'station' => $stopover->station?->name,
                    'station_id' => $stopover->train_station_id,
                    'identifier_id' => $stopover->station_identifier_id,
                    'station_lat' => $location->latitude,
                    'station_lon' => $location->longitude,
                    'closest_lat' => $coordinates[$bestIndex]->latitude,
                    'closest_lon' => $coordinates[$bestIndex]->longitude,
                    'closest_index' => $bestIndex,
                    'snap_distance_m' => $snapDistance,
                    'limit_m' => self::MAX_SNAP_DISTANCE_METERS,
                    'snapped_before_failure' => count($indices),
                    'search_started_at_index' => $cursor,
                ]);

                return null;
            }

            Log::debug('ProviderPolyline: Snapped stopover', [
                'station' => $stopover->station?->name,
                'station_id' => $stopover->train_station_id,
                'geometry_index' => $bestIndex,
                'searched_from_index' => $cursor,
                'snap_distance_m' => $snapDistance,
                'arrival' => $stopover->arrival_planned?->toIso8601String(),
                'departure' => $stopover->departure_planned?->toIso8601String(),
            ]);

            $indices[] = $bestIndex;
            $snapDistances[] = $snapDistance;
            $cursor = $bestIndex;
        }

        Log::debug('ProviderPolyline: Snapped all stopovers onto geometry', [
            'stopovers' => count($indices),
            'geometry_points' => $lastIndex + 1,
            'snap_distance_max_m' => max($snapDistances),
            'snap_distance_avg_m' => (int) round(array_sum($snapDistances) / count($snapDistances)),
            'first_index' => $indices[0],
            'last_index' => end($indices),
            // A last index well below the final point means the shape reaches beyond the trip
            'geometry_covered_percent' => $lastIndex > 0 ? round((end($indices) - $indices[0]) / $lastIndex * 100, 1) : 0.0,
        ]);

        return $indices;
    }

    /**
     * Straight-line distance between two stopovers, used to judge how much of a detour the
     * sliced shape describes. Returns 0 when either side has no usable coordinates.
     */
    private function beelineDistance(Stopover $start, Stopover $end): int
    {
        $from = $start->coordinate;
        $to = $end->coordinate;

        if ($from === null || $to === null) {
            return 0;
        }

        return $this->geodesicService->haversineDistance($from, $to);
    }

    /**
     * @return array{coordinates: Coordinate[], precision: int}|null
     */
    private function fetchGeometry(Trip $trip): ?array
    {
        Log::debug('ProviderPolyline: Requesting raw journey from provider', [
            'trip_id' => $trip->id,
            'provider_trip_id' => $trip->trip_id,
        ]);

        // CachedDataProvider does not pass raw journeys through, so ask the provider directly.
        $rawJourney = new DataProviderBuilder()->build(cache: false)
            ->fetchRawHafasTrip($trip->trip_id, $trip->linename);

        $legs = $rawJourney['legs'] ?? [];
        $geometry = null;
        foreach ($legs as $leg) {
            // Match strictly: a leg of a neighbouring trip would describe the wrong line.
            if (($leg['tripId'] ?? null) === $trip->trip_id) {
                $geometry = $leg['legGeometry'] ?? null;
                break;
            }
        }

        if (empty($geometry['points'])) {
            Log::debug('ProviderPolyline: No leg geometry in provider response', [
                'trip_id' => $trip->id,
                'provider_trip_id' => $trip->trip_id,
                'legs_returned' => count($legs),
                'leg_matched' => $geometry !== null,
                'returned_leg_trip_ids' => array_map(static fn (array $leg): ?string => $leg['tripId'] ?? null, $legs),
            ]);

            return null;
        }

        $precision = (int) ($geometry['precision'] ?? 5);
        $locations = new PolylineTranscoder()->decodePolyline($geometry['points'], $precision);

        $coordinates = [];
        foreach ($locations as $location) {
            $coordinates[] = new Coordinate($location->getLatitude(), $location->getLongitude());
        }

        if (count($coordinates) < 2) {
            Log::debug('ProviderPolyline: Decoded geometry is too short to slice', [
                'trip_id' => $trip->id,
                'points' => count($coordinates),
            ]);

            return null;
        }

        $length = $this->geodesicService->pathLength($coordinates);
        $first = $coordinates[0];
        $last = $coordinates[count($coordinates) - 1];

        Log::debug('ProviderPolyline: Decoded leg geometry', [
            'trip_id' => $trip->id,
            'legs_returned' => count($legs),
            'points' => count($coordinates),
            'precision' => $precision,
            'encoded_bytes' => strlen($geometry['points']),
            'length_km' => round($length / 1000, 2),
            'points_per_km' => $length > 0 ? round(count($coordinates) / ($length / 1000), 1) : null,
            'starts_at' => (string) $first,
            'ends_at' => (string) $last,
        ]);

        return ['coordinates' => $coordinates, 'precision' => $precision];
    }
}
