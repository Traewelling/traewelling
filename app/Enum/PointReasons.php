<?php
declare(strict_types=0);

namespace App\Enum;

enum PointReasons: int
{
    case IN_TIME        = 0;
    case GOOD_ENOUGH    = 1;
    case NOT_SUFFICIENT = 2;
    case FORCED         = 3;
}
