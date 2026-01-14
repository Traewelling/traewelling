<?php

declare(strict_types=1);

namespace App\Dto\Internal;

readonly class GlobalCheckinStats
{
    public function __construct(
        public int $distance,
        public int $duration,
        public int $userCount,
    ) {}
}
