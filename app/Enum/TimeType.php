<?php
declare(strict_types=1);

namespace App\Enum;

enum TimeType: int
{
    case PLANNED  = 0;
    case REALTIME = 1;
    case MANUAL   = 9;

    private function getTooltipStringId($timeType): string {
        return match ($timeType) {
            self::PLANNED  => 'time-is-planned',
            self::REALTIME => 'time-is-real',
            self::MANUAL   => 'time-is-manual'
        };
    }

    public function getTooltip(): string {
        return __(self::getTooltipStringId($this));
    }
}
