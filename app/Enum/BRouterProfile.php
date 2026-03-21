<?php

declare(strict_types=1);

namespace App\Enum;

enum BRouterProfile: string
{
    /** Standard rail routing — used for trains, trams, subways, etc. */
    case RAIL = 'rail';

    /** Road routing — used for bus routes. */
    case ROAD = 'car-fast';
}
