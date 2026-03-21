<?php

declare(strict_types=1);

use App\Dto\Coordinate;
use App\Models\RouteSegment;
use App\Services\GeoService;
use Illuminate\Database\Migrations\Migration;
use Traewelling\GooglePolyline\PolylineTranscoder;

return new class() extends Migration
{
    /**
     * Delete route segments whose polyline start/end is further than the
     * BRouter endpoint tolerance from the stored waypoints (station or
     * identifier coordinates). Such segments were created before the
     * validation was introduced and would not be saved today.
     */
    public function up(): void
    {
        $tolerance = (int) config('services.brouter.endpoint_tolerance_meters', 200);
        $geo = new GeoService();
        $transcoder = new PolylineTranscoder();

        RouteSegment::with(['fromStation', 'toStation', 'fromIdentifier', 'toIdentifier'])
            ->chunkById(200, function (iterable $segments) use ($tolerance, $geo, $transcoder): void {
                foreach ($segments as $segment) {
                    if ($this->shouldDelete($segment, $tolerance, $geo, $transcoder)) {
                        $segment->delete();
                    }
                }
            });
    }

    private function shouldDelete(
        RouteSegment $segment,
        int $tolerance,
        GeoService $geo,
        PolylineTranscoder $transcoder,
    ): bool {
        if (empty($segment->polyline)) {
            return true;
        }

        try {
            $precision = $segment->polyline_precision ?? 5;
            $locations = $transcoder->decodePolyline($segment->polyline, $precision);
        } catch (Throwable) {
            // Malformed polyline: remove it.
            return true;
        }

        if (count($locations) < 2) {
            return true;
        }

        $locations = array_values($locations);
        $first = new Coordinate($locations[0]->getLatitude(), $locations[0]->getLongitude());
        $last = new Coordinate($locations[array_key_last($locations)]->getLatitude(), $locations[array_key_last($locations)]->getLongitude());

        $fromWaypoint = $segment->fromIdentifier
            ? new Coordinate((float) $segment->fromIdentifier->latitude, (float) $segment->fromIdentifier->longitude)
            : new Coordinate((float) $segment->fromStation->latitude, (float) $segment->fromStation->longitude);

        $toWaypoint = $segment->toIdentifier
            ? new Coordinate((float) $segment->toIdentifier->latitude, (float) $segment->toIdentifier->longitude)
            : new Coordinate((float) $segment->toStation->latitude, (float) $segment->toStation->longitude);

        return $geo->getDistance($fromWaypoint, $first) > $tolerance
            || $geo->getDistance($toWaypoint, $last) > $tolerance;
    }
};
