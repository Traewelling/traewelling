<?php
declare(strict_types=1);

namespace App\Enum;

enum Business: int
{
    case PRIVATE = 0;
    case BUSINESS = 1;
    case COMMUTE = 2;
}
