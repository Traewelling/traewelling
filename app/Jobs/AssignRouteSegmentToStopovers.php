<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\Queue;
use App\Models\RouteSegment;
use App\Models\Stopover;
use App\Repositories\TripRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class AssignRouteSegmentToStopovers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 600;

    public function __construct(private readonly string $segmentId)
    {
        $this->onQueue(Queue::BACKGROUND->value);
    }

    public function handle(TripRepository $tripRepository): void
    {
        $segment = RouteSegment::find($this->segmentId);
        if ($segment === null) {
            Log::info('AssignRouteSegmentToStopovers: Segment no longer exists, skipping', ['segment_id' => $this->segmentId]);

            return;
        }

        $segmentDuration = $segment->duration ?? 0;
        $tolerance = max(300, (int) round($segmentDuration * 0.1));
        $assigned = 0;
        $candidates = 0;
        $skippedPathType = 0;
        $skippedNextStop = 0;
        $skippedNoTime = 0;
        $skippedDuration = 0;
        $affectedTrips = [];

        // When the segment has identifier specificity, match stopovers by identifier.
        // Otherwise fall back to station-based matching.
        $fromIdentifierId = $segment->from_identifier_id;
        $toIdentifierId = $segment->to_identifier_id;
        $useIdentifierMatching = $fromIdentifierId !== null && $toIdentifierId !== null;

        Log::debug('AssignRouteSegmentToStopovers: Starting', [
            'segment_id' => $segment->id,
            'from_station_id' => $segment->from_station_id,
            'to_station_id' => $segment->to_station_id,
            'from_identifier_id' => $fromIdentifierId,
            'to_identifier_id' => $toIdentifierId,
            'use_identifier_matching' => $useIdentifierMatching,
            'duration_s' => $segmentDuration,
            'tolerance_s' => $tolerance,
            'path_type' => $segment->path_type,
        ]);

        $query = Stopover::whereNull('route_segment_id')->with('trip');
        if ($useIdentifierMatching) {
            $query->where('station_identifier_id', $fromIdentifierId);
        } else {
            $query->where('train_station_id', $segment->from_station_id);
        }

        $query->chunkById(200, function (Collection $fromStopovers) use (
            $segment, $segmentDuration, $tolerance, $tripRepository,
            $useIdentifierMatching, $toIdentifierId,
            &$candidates, &$assigned, &$affectedTrips,
            &$skippedPathType, &$skippedNextStop, &$skippedNoTime, &$skippedDuration,
        ): void {
            // Batch-load ALL stopovers for all trips in this chunk.
            // We need every stop to determine which stop is immediately next after each fromStop,
            // since on circular routes the to_station may appear many times throughout the trip.
            $candidates += $fromStopovers->count();
            $tripIds = $fromStopovers->pluck('trip_id')->unique();
            $allTripStopovers = Stopover::whereIn('trip_id', $tripIds)
                ->orderBy('arrival_planned')
                ->get(['id', 'trip_id', 'train_station_id', 'station_identifier_id', 'arrival_planned', 'departure_planned'])
                ->groupBy('trip_id');

            foreach ($fromStopovers as $fromStop) {
                // If the segment has a path_type, only assign to trips with a matching transport mode.
                // A null path_type on the segment means it is universal and matches any trip.
                if ($segment->path_type !== null) {
                    $tripPathType = $fromStop->trip?->category?->getBrouterProfile();
                    if ($tripPathType === null || $tripPathType->value !== $segment->path_type) {
                        $skippedPathType++;

                        continue;
                    }
                }

                $startTime = $fromStop->departure_planned ?? $fromStop->arrival_planned;
                $allStopoversForTrip = $allTripStopovers->get($fromStop->trip_id, collect());

                // Find the immediately next stop in the trip after the fromStop.
                // On circular routes the to_station may appear many times only a direct
                // consecutive connection qualifies for this segment.
                $nextStop = $allStopoversForTrip
                    ->filter(function (Stopover $ts) use ($startTime): bool {
                        if (!$startTime) {
                            return true;
                        }

                        return $ts->arrival_planned?->gt($startTime)
                            || $ts->departure_planned?->gt($startTime);
                    })
                    ->sortBy('arrival_planned')
                    ->first();

                // Match the next stop by identifier (precise) or by station (fallback).
                $nextStopMatches = $useIdentifierMatching
                    ? ($nextStop?->station_identifier_id === $toIdentifierId)
                    : ($nextStop?->train_station_id === $segment->to_station_id);

                if (!$nextStop || !$nextStopMatches) {
                    $skippedNextStop++;

                    continue;
                }

                $endTime = $nextStop->arrival_planned ?? $nextStop->departure_planned;

                if (!$startTime || !$endTime) {
                    $skippedNoTime++;

                    continue;
                }

                $duration = (int) round($startTime->diffInSeconds($endTime));

                if ($duration < max(0, $segmentDuration - $tolerance) || $duration > $segmentDuration + $tolerance) {
                    $skippedDuration++;

                    continue;
                }

                $tripRepository->setRouteSegmentForStop($fromStop, $segment);
                $assigned++;
                $affectedTrips[$fromStop->trip_id] = true;
            }
        });

        foreach (array_keys($affectedTrips) as $tripId) {
            RecalculateStatusesDistanceForTrip::dispatch($tripId);
        }

        Log::info('AssignRouteSegmentToStopovers: Completed', [
            'segment_id' => $segment->id,
            'candidates' => $candidates,
            'assigned' => $assigned,
            'trips_queued' => count($affectedTrips),
            'skipped_path_type' => $skippedPathType,
            'skipped_next_stop' => $skippedNextStop,
            'skipped_no_time' => $skippedNoTime,
            'skipped_duration' => $skippedDuration,
        ]);
    }
}
