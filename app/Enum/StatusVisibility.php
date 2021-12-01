<?php
declare(strict_types=1);

namespace App\Enum;

enum StatusVisibility: int
{
    case PUBLIC = 0;
    case UNLISTED = 1;
    case FOLLOWERS = 2;
    case PRIVATE = 3;
}
