<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\PointReason;

class PointCalculation
{
    public readonly int $points;

    public readonly int $base;

    public readonly int $distance;

    public readonly PointReason $reason;

    public readonly float $factor;

    public function __construct(
        int $points,
        int $base,
        int $distance,
        PointReason $reason,
        float $factor,
    ) {
        $this->points = $points;
        $this->base = $base;
        $this->distance = $distance;
        $this->reason = $reason;
        $this->factor = $factor;
    }
}
