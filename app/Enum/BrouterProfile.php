<?php
declare(strict_types=1);

namespace App\Enum;

enum BrouterProfile: string
{
    case RAIL         = 'rail';
    case CAR_FAST     = 'car-fast';
    case CAR_SHORTEST = 'car-shortest';
}
