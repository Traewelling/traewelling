<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StationUsageMoveResultDto',
    required: ['stopovers', 'trips', 'events', 'eventSuggestions', 'routeSegments', 'homeUsers'],
    properties: [
        new OA\Property(property: 'stopovers', description: 'Number of moved stopovers, including duplicates that were merged into existing stopovers on the target station', type: 'integer', example: 12),
        new OA\Property(property: 'trips', description: 'Number of moved trip origin/destination references', type: 'integer', example: 3),
        new OA\Property(property: 'events', type: 'integer', example: 0),
        new OA\Property(property: 'eventSuggestions', type: 'integer', example: 0),
        new OA\Property(property: 'routeSegments', description: 'Number of moved route segment sides. Only sides without an identifier binding are moved, identifier-bound sides follow their identifier', type: 'integer', example: 4),
        new OA\Property(property: 'homeUsers', description: 'Number of users whose home station was moved', type: 'integer', example: 1),
    ],
    type: 'object'
)]
readonly class StationUsageMoveResultDto
{
    public const array MOVABLE_TYPES = ['stopovers', 'trips', 'events', 'eventSuggestions', 'routeSegments', 'homeUsers'];

    public function __construct(
        public int $stopovers,
        public int $trips,
        public int $events,
        public int $eventSuggestions,
        public int $routeSegments,
        public int $homeUsers,
    ) {}
}
