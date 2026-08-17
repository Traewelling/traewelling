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

    public function hasData(): bool
    {
        return $this->distance > 0 && $this->duration > 0 && $this->userCount > 0;
    }

    public function distanceInMillionKilometers(): float
    {
        return round($this->distance / 1000 / 1000 / 1000, 1);
    }

    public function durationInYears(): float
    {
        return round($this->duration / 60 / 60 / 24 / 365, 1);
    }
}
