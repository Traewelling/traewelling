<?php declare(strict_types=1);

namespace App\Enum\Report;

enum ReportReason: string
{
    case INAPPROPRIATE = 'inappropriate';
    case IMPLAUSIBLE   = 'implausible';
    case SPAM          = 'spam';
    case ILLEGAL       = 'illegal';
    case OTHER         = 'other';

    public function getPriority(): int {
        return match ($this) {
            self::IMPLAUSIBLE   => 0,
            self::SPAM          => 10,
            self::OTHER         => 20,
            self::INAPPROPRIATE => 50,
            self::ILLEGAL       => 100,
        };
    }
}
