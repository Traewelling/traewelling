<?php

declare(strict_types=1);

namespace App\Dto;

use App\Models\Stopover;

/**
 * A pair of stopovers representing the same physical stop at the same planned
 * time in one trip: the later-created {@see self::$duplicate} is the spurious
 * real-time addition, {@see self::$keeper} is the original that is kept.
 */
readonly class DuplicateStopoverPair
{
    public function __construct(
        public Stopover $duplicate,
        public Stopover $keeper,
    ) {}
}
