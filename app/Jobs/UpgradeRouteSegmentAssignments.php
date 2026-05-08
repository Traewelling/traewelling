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

/**
 * For a newly created identifier-to-identifier route segment, find stopovers that
 * are currently assigned to a station-to-station segment for the same station pair
 * and upgrade them to this more precise segment when the identifier pair matches.
 *
 * Also covers stopovers that are currently unassigned (complementing AssignRouteSegmentToStopovers).
 */
class UpgradeRouteSegmentAssignments implements ShouldQueue
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
        if ($segment === null || $segment->from_identifier_id === null || $segment->to_identifier_id === null) {
            Log::info('UpgradeRouteSegmentAssignments: Segment missing or not identifier-based, skipping', [
                'segment_id' => $this->segmentId,
            ]);

            return;
        }

        $segmentDuration = $segment->duration ?? 0;
        $tolerance = max(300, (int) round($segmentDuration * 0.1));
        $upgraded = 0;
        $affectedTrips = [];
        $replacedSegmentIds = [];

        // Find stopovers at the from-identifier that either:
        // (a) have no segment yet, or
        // (b) are assigned to a station-to-station segment (from_identifier_id IS NULL)
        Stopover::where('station_identifier_id', $segment->from_identifier_id)
            ->where(function ($q) {
                $q->whereNull('route_segment_id')
                    ->orWhereHas('routeSegment', fn ($q2) => $q2->whereNull('from_identifier_id'));
            })
            ->with('trip')
            ->chunkById(200, function (Collection $fromStopovers) use (
                $segment, $segmentDuration, $tolerance, $tripRepository,
                &$upgraded, &$affectedTrips,
            ): void {
                $tripIds = $fromStopovers->pluck('trip_id')->unique();
                $allTripStopovers = Stopover::whereIn('trip_id', $tripIds)
                    ->orderBy('arrival_planned')
                    ->get(['id', 'trip_id', 'train_station_id', 'station_identifier_id', 'arrival_planned', 'departure_planned', 'route_segment_id'])
                    ->groupBy('trip_id');

                foreach ($fromStopovers as $fromStop) {
                    if ($segment->path_type !== null) {
                        $tripPathType = $fromStop->trip?->category?->getSegmentPathType();
                        if ($tripPathType === null || $tripPathType->value !== $segment->path_type) {
                            continue;
                        }
                    }

                    $startTime = $fromStop->departure_planned ?? $fromStop->arrival_planned;
                    $allStopoversForTrip = $allTripStopovers->get($fromStop->trip_id, collect());

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

                    if ($nextStop?->station_identifier_id !== $segment->to_identifier_id) {
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

                    if ($fromStop->route_segment_id !== null) {
                        $replacedSegmentIds[$fromStop->route_segment_id] = true;
                    }

                    $tripRepository->setRouteSegmentForStop($fromStop, $segment);
                    $upgraded++;
                    $affectedTrips[$fromStop->trip_id] = true;
                }
            });

        foreach (array_keys($affectedTrips) as $tripId) {
            RecalculateStatusesDistanceForTrip::dispatch($tripId);
        }

        foreach (array_keys($replacedSegmentIds) as $oldSegmentId) {
            if (!Stopover::where('route_segment_id', $oldSegmentId)->exists()) {
                DeleteRouteSegment::dispatch($oldSegmentId);
                Log::info('UpgradeRouteSegmentAssignments: Old segment is now unused, dispatching delete', [
                    'old_segment_id' => $oldSegmentId,
                ]);
            }
        }

        Log::info('UpgradeRouteSegmentAssignments: Completed', [
            'segment_id' => $segment->id,
            'upgraded' => $upgraded,
            'trips_queued' => count($affectedTrips),
        ]);
    }
}
