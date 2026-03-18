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

class DeleteRouteSegment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, IsMonitored, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 600;

    public function __construct(private readonly RouteSegment $segment)
    {
        $this->onQueue('low');
    }

    public function handle(TripRepository $tripRepository): void
    {
        $segment = $this->segment;
        $segmentId = $segment->id;
        $affectedTrips = [];
        $reassigned = 0;
        $nulled = 0;

        Stopover::where('route_segment_id', $segmentId)
            ->with('trip')
            ->chunkById(200, function (Collection $stopovers) use (
                $segment, $segmentId, $tripRepository, &$affectedTrips, &$reassigned, &$nulled,
            ): void {
                $tripIds = $stopovers->pluck('trip_id')->unique();
                $allTripStopovers = Stopover::whereIn('trip_id', $tripIds)
                    ->orderBy('arrival_planned')
                    ->get(['id', 'trip_id', 'train_station_id', 'arrival_planned', 'departure_planned'])
                    ->groupBy('trip_id');

                foreach ($stopovers as $fromStop) {
                    $startTime = $fromStop->departure_planned ?? $fromStop->arrival_planned;
                    $allStopoversForTrip = $allTripStopovers->get($fromStop->trip_id, collect());

                    // Find the immediately next stop in the trip after the fromStop.
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
                        // Can't determine next stop: null out assignment.
                        $fromStop->route_segment_id = null;
                        $fromStop->save();
                        $nulled++;
                        $affectedTrips[$fromStop->trip_id] = true;

                        continue;
                    }

                    $endTime = $nextStop->arrival_planned ?? $nextStop->departure_planned;

                    if (!$startTime || !$endTime) {
                        $fromStop->route_segment_id = null;
                        $fromStop->save();
                        $nulled++;
                        $affectedTrips[$fromStop->trip_id] = true;

                        continue;
                    }

                    $duration = (int) round($startTime->diffInSeconds($endTime));
                    $pathType = $fromStop->trip?->category?->getORRProfile();

                    $replacement = $tripRepository->getRouteSegmentBetweenStops(
                        start: $fromStop,
                        end: $nextStop,
                        duration: $duration,
                        pathType: $pathType,
                        excludeId: $segmentId,
                    );

                    if ($replacement !== null) {
                        $tripRepository->setRouteSegmentForStop($fromStop, $replacement);
                        $reassigned++;
                    } else {
                        $fromStop->route_segment_id = null;
                        $fromStop->save();
                        $nulled++;
                    }

                    $affectedTrips[$fromStop->trip_id] = true;
                }
            });

        $segment->delete();

        foreach (array_keys($affectedTrips) as $tripId) {
            RecalculateStatusesDistanceForTrip::dispatch($tripId);
        }

        Log::info('DeleteRouteSegment: Completed', [
            'segment_id' => $segmentId,
            'reassigned' => $reassigned,
            'nulled' => $nulled,
            'trips_queued' => count($affectedTrips),
        ]);
    }
}
