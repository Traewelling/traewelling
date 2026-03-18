<?php

declare(strict_types=1);

namespace App\Jobs;

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

    public $queue = 'low';

    public function __construct(private readonly RouteSegment $segment) {}

    public function handle(TripRepository $tripRepository): void
    {
        $segment = $this->segment;
        $segmentDuration = $segment->duration ?? 0;
        $tolerance = max(300, (int) round($segmentDuration * 0.1));
        $assigned = 0;
        $affectedTrips = [];

        Stopover::where('train_station_id', $segment->from_station_id)
            ->whereNull('route_segment_id')
            ->with('trip')
            ->chunkById(200, function (Collection $fromStopovers) use (
                $segment, $segmentDuration, $tolerance, $tripRepository, &$assigned, &$affectedTrips,
            ): void {
                // Batch-load ALL stopovers for all trips in this chunk.
                // We need every stop to determine which stop is immediately next after each fromStop,
                // since on circular routes the to_station may appear many times throughout the trip.
                $tripIds = $fromStopovers->pluck('trip_id')->unique();
                $allTripStopovers = Stopover::whereIn('trip_id', $tripIds)
                    ->orderBy('arrival_planned')
                    ->get(['id', 'trip_id', 'train_station_id', 'arrival_planned', 'departure_planned'])
                    ->groupBy('trip_id');

                foreach ($fromStopovers as $fromStop) {
                    // If the segment has a path_type, only assign to trips with a matching transport mode.
                    // A null path_type on the segment means it is universal and matches any trip.
                    if ($segment->path_type !== null) {
                        $tripPathType = $fromStop->trip?->category?->getORRProfile();
                        if ($tripPathType === null || $tripPathType->value !== $segment->path_type) {
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

                    if (!$nextStop || $nextStop->train_station_id !== $segment->to_station_id) {
                        continue;
                    }

                    $endTime = $nextStop->arrival_planned ?? $nextStop->departure_planned;

                    if (!$startTime || !$endTime) {
                        continue;
                    }

                    $duration = (int) round($startTime->diffInSeconds($endTime));

                    if ($duration < max(0, $segmentDuration - $tolerance) || $duration > $segmentDuration + $tolerance) {
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
            'assigned' => $assigned,
            'trips_queued' => count($affectedTrips),
        ]);
    }
}
