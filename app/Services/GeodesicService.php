<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\Coordinate;

class GeodesicService
{
    private const EARTH_RADIUS_METERS = 6_371_000;

    /**
     * Generate evenly-spaced points along the great-circle arc between two coordinates
     * using Spherical Linear Interpolation (SLERP) on the unit sphere.
     *
     * @return Coordinate[]
     */
    public function interpolate(Coordinate $from, Coordinate $to, int $points = 100): array
    {
        $lat1 = deg2rad($from->latitude);
        $lon1 = deg2rad($from->longitude);
        $lat2 = deg2rad($to->latitude);
        $lon2 = deg2rad($to->longitude);

        // Convert to 3D Cartesian unit-sphere coordinates.
        $ax = cos($lat1) * cos($lon1);
        $ay = cos($lat1) * sin($lon1);
        $az = sin($lat1);

        $bx = cos($lat2) * cos($lon2);
        $by = cos($lat2) * sin($lon2);
        $bz = sin($lat2);

        // Central angle between the two points.
        $dot = max(-1.0, min(1.0, $ax * $bx + $ay * $by + $az * $bz));
        $omega = acos($dot);

        $result = [];
        for ($i = 0; $i < $points; $i++) {
            $t = $points === 1 ? 0.0 : $i / ($points - 1);

            if (abs($omega) < 1e-10) {
                // Points are effectively identical — no interpolation needed.
                $px = $ax;
                $py = $ay;
                $pz = $az;
            } else {
                $sinOmega = sin($omega);
                $scale0 = sin((1.0 - $t) * $omega) / $sinOmega;
                $scale1 = sin($t * $omega) / $sinOmega;

                $px = $scale0 * $ax + $scale1 * $bx;
                $py = $scale0 * $ay + $scale1 * $by;
                $pz = $scale0 * $az + $scale1 * $bz;
            }

            $lat = rad2deg(asin(max(-1.0, min(1.0, $pz))));
            $lon = rad2deg(atan2($py, $px));

            $result[] = new Coordinate($lat, $lon);
        }

        return $result;
    }

    /**
     * Compute the great-circle distance between two coordinates in meters (Haversine formula).
     */
    public function haversineDistance(Coordinate $from, Coordinate $to): int
    {
        return (int) round($this->exactHaversineDistance($from, $to));
    }

    /**
     * Find the index of the path point closest to the given coordinate.
     *
     * The search starts at $fromIndex, so a caller walking a path in order can pass the previously
     * matched index and stay on track where a route touches the same place twice.
     *
     * @param  Coordinate[]  $path
     * @return int|null null when the path holds no point at or after $fromIndex
     */
    public function findNearestPointIndex(Coordinate $needle, array $path, int $fromIndex = 0): ?int
    {
        $path = array_values($path);
        $bestIndex = null;
        $bestDistance = PHP_FLOAT_MAX;

        for ($i = max(0, $fromIndex), $count = count($path); $i < $count; $i++) {
            $distance = $this->approximateSquaredDistance($needle, $path[$i]);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestIndex = $i;
            }
        }

        return $bestIndex;
    }

    /**
     * Total length of a path in meters, summed over its segments.
     *
     * @param  Coordinate[]  $coordinates
     */
    public function pathLength(array $coordinates): float
    {
        $coordinates = array_values($coordinates);
        $length = 0.0;

        for ($i = 1, $count = count($coordinates); $i < $count; $i++) {
            $length += $this->exactHaversineDistance($coordinates[$i - 1], $coordinates[$i]);
        }

        return $length;
    }

    /**
     * Cheap equirectangular approximation, squared and without any earth radius applied. Useless as
     * a distance, but it grows monotonically with the real one, so it ranks candidates correctly
     * without paying for the trigonometry a full haversine per point would cost.
     */
    private function approximateSquaredDistance(Coordinate $from, Coordinate $to): float
    {
        $x = ($from->longitude - $to->longitude) * cos(deg2rad(($from->latitude + $to->latitude) / 2));
        $y = $from->latitude - $to->latitude;

        return $x * $x + $y * $y;
    }

    private function exactHaversineDistance(Coordinate $from, Coordinate $to): float
    {
        $lat1 = deg2rad($from->latitude);
        $lat2 = deg2rad($to->latitude);
        $deltaLat = deg2rad($to->latitude - $from->latitude);
        $deltaLon = deg2rad($to->longitude - $from->longitude);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
        $c = 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }
}
