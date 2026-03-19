<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\AssignRouteSegmentToStopovers;
use App\Jobs\RecalculateStatusesDistanceForTrip;
use App\Models\RouteSegment;
use App\Models\Stopover;

class RouteSegmentObserver
{
    public function created(RouteSegment $routeSegment): void
    {
        AssignRouteSegmentToStopovers::dispatch($routeSegment);
    }

    public function updated(RouteSegment $routeSegment): void
    {
        if (!$routeSegment->wasChanged(['polyline', 'distance'])) {
            return;
        }

        Stopover::where('route_segment_id', $routeSegment->id)
            ->distinct()
            ->pluck('trip_id')
            ->each(fn (string $tripId) => RecalculateStatusesDistanceForTrip::dispatch($tripId));
    }
}
