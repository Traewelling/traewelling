<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\AssignRouteSegmentToStopovers;
use App\Models\RouteSegment;

class RouteSegmentObserver
{
    public function created(RouteSegment $routeSegment): void
    {
        AssignRouteSegmentToStopovers::dispatch($routeSegment);
    }
}
