<?php

namespace App\Objects;

use App\Dto\Coordinate;

class LineSegment
{
    public readonly Coordinate $start;
    public readonly Coordinate $finish;
    private int                $distance;

    public function __construct(Coordinate $start, Coordinate $finish) {
        $this->start  = $start;
        $this->finish = $finish;
    }

    public function calculateDistance(): int {
        if (
            $this->start->longitude === $this->finish->longitude
            && $this->start->latitude === $this->finish->latitude
        ) {
            return 0.0;
        }

        $equatorialRadiusInMeters = 6378137;

        $latA           = $this->start->latitude / 180 * M_PI;
        $lonA           = $this->start->longitude / 180 * M_PI;
        $latB           = $this->finish->latitude / 180 * M_PI;
        $lonB           = $this->finish->longitude / 180 * M_PI;
        $this->distance = round(acos(sin($latA) * sin($latB) + cos($latA) * cos($latB) * cos($lonB - $lonA))
                                * $equatorialRadiusInMeters);

        return $this->distance;
    }

    public function interpolatePoint(float $percent): Coordinate {
        return new Coordinate(
            round($this->start->latitude + $percent * ($this->finish->latitude - $this->start->latitude), 6),
            round($this->start->longitude + $percent * ($this->finish->longitude - $this->start->longitude), 6)
        );
    }
}
