<?php
declare(strict_types=0);

namespace App\Enum;

final class PointReasons extends BasicEnum
{
    public const IN_TIME        = 0;
    public const GOOD_ENOUGH    = 1;
    public const NOT_SUFFICIENT = 2;
    public const FORCED         = 3;
}
