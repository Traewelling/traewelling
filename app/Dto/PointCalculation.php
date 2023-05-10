<?php declare(strict_types=1);

namespace App\Dto;

use App\Enum\PointReason;

class PointCalculation
{

    public readonly int         $points;
    public readonly int         $basePoints;
    public readonly int         $distancePoints;
    public readonly PointReason $reason;
    public readonly int         $factor;

    public function __construct(
        int         $points,
        int         $basePoints,
        int         $distancePoints,
        PointReason $reason,
        int         $factor,
    ) {
        $this->points           = $points;
        $this->basePoints       = $basePoints;
        $this->distancePoints   = $distancePoints;
        $this->reason           = $reason;
        $this->factor           = $factor;
    }

}
