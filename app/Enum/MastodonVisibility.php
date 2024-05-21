<?php
declare(strict_types=1);

namespace App\Enum;

enum MastodonVisibility: int
{
    case PUBLIC   = 0;
    case UNLISTED = 1;
    case PRIVATE  = 2;
    case DIRECT   = 3;
}
