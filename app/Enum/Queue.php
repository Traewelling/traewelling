<?php

declare(strict_types=1);

namespace App\Enum;

enum Queue: string
{
    /** Time-critical operations (emails, Mastodon posts) */
    case REALTIME = 'realtime';

    /** High-priority operations (trip refreshes) */
    case IMPORTANT = 'important';

    /** Default priority for most jobs */
    case NORMAL = 'normal';

    /** Lower-priority, non-urgent work (polylines, route segments) */
    case LOW = 'low';

    /** Maintenance and housekeeping tasks */
    case BACKGROUND = 'background';

    public function __toString(): string
    {
        return $this->value;
    }
}
