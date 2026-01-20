<?php

namespace App\Objects;

use App\Dto\Coordinate;
use App\Services\GeoService;

readonly class LineSegment
{
    public Coordinate $start;

    public Coordinate $finish;

    public function __construct(Coordinate $start, Coordinate $finish)
    {
        $this->start = $start;
        $this->finish = $finish;
    }

    /**
     * @deprecated Use GeoService::calculateDistance instead
     */
    public function calculateDistance(): int
    {
        return (new GeoService())->getDistance($this->start, $this->finish);
    }

    /**
     * @deprecated Use GeoService::interpolatePoint instead
     */
    public function interpolatePoint(float $percent): ?Coordinate
    {
        return (new GeoService())->interpolatePoint($this->start, $this->finish, $percent);
    }
}
