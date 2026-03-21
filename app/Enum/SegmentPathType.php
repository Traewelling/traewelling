<?php

declare(strict_types=1);

namespace App\Enum;

enum SegmentPathType: string
{
    /** Rail routing via BRouter: used for trains, trams, subways, etc. */
    case RAIL = 'rail';

    /** Road routing via BRouter: used for bus routes. */
    case ROAD = 'car-fast';

    /** Great-circle interpolation: used for flights. No external routing service required. */
    case GREAT_CIRCLE = 'great-circle';

    /**
     * Returns the BRouter profile to use for this path type,
     * or null if BRouter is not applicable (e.g. great-circle arcs).
     */
    public function getBRouterProfile(): ?BRouterProfile
    {
        return match ($this) {
            self::RAIL => BRouterProfile::RAIL,
            self::ROAD => BRouterProfile::ROAD,
            default => null,
        };
    }
}
