<?php

declare(strict_types=1);

namespace App\Dto;

readonly class IdentifierMoveResult
{
    public function __construct(
        public int $movedStopovers,
        public int $skippedStopovers,
        public int $updatedTrips,
        public int $updatedRouteSegments,
    ) {}
}
