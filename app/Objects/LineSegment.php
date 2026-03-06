<?php

namespace App\Objects;

use App\Dto\Coordinate;

readonly class LineSegment
{
    public Coordinate $start;

    public Coordinate $finish;

    public function __construct(Coordinate $start, Coordinate $finish)
    {
        $this->start = $start;
        $this->finish = $finish;
    }

}
