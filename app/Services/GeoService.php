<?php

namespace App\Services;

use App\Dto\BoundingBox;
use App\Dto\Coordinate;

class GeoService
{
    private const int EQUATORIAL_RADIUS_IN_METERS = 6378137;

    public function getDistance(Coordinate $start, Coordinate $end): float
    {
        if ($start->longitude === $end->longitude && $start->latitude === $end->latitude) {
            return 0.0;
        }

        $latA = $start->latitude / 180 * M_PI;
        $lonA = $start->longitude / 180 * M_PI;
        $latB = $end->latitude / 180 * M_PI;
        $lonB = $end->longitude / 180 * M_PI;

        return round(acos(sin($latA) * sin($latB) + cos($latA) * cos($latB) * cos($lonB - $lonA))
                     * self::EQUATORIAL_RADIUS_IN_METERS);
    }

    public function getBoundingBox(Coordinate $center, int $radius, int $precision = 6): BoundingBox
    {
        $lat = deg2rad($center->latitude);
        $lon = deg2rad($center->longitude);
        $d = $radius / self::EQUATORIAL_RADIUS_IN_METERS;

        $latMin = $lat - $d;
        $latMax = $lat + $d;

        $lonT = asin(sin($d) / cos($lat));
        $lonMin = $lon - $lonT;
        $lonMax = $lon + $lonT;

        return new BoundingBox(
            new Coordinate(round(rad2deg($latMax), $precision), round(rad2deg($lonMax), $precision)),
            new Coordinate(round(rad2deg($latMin), $precision), round(rad2deg($lonMin), $precision))
        );
    }

    public function interpolatePoint(?Coordinate $start, ?Coordinate $end, float $percent): ?Coordinate
    {
        if ($start === null || $end === null) {
            return $start ?? $end ?? null;
        }

        return new Coordinate(
            round($start->latitude + $percent * ($end->latitude - $start->latitude), 6),
            round($start->longitude + $percent * ($end->longitude - $start->longitude), 6)
        );
    }
}
