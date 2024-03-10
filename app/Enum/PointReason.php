<?php
declare(strict_types=1);

namespace App\Enum;

enum PointReason: int
{
    case IN_TIME        = 0;
    case GOOD_ENOUGH    = 1;
    case NOT_SUFFICIENT = 2;
    case FORCED         = 3;

    /**
     * Trip was manually created by the user => no points.
     */
    case MANUAL_TRIP = 4;
}
