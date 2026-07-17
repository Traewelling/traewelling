<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StationUsageDto',
    required: ['stopovers', 'trips', 'events', 'eventSuggestions', 'identifiers', 'routeSegments', 'homeUsers'],
    properties: [
        new OA\Property(property: 'stopovers', type: 'integer', example: 12),
        new OA\Property(property: 'trips', description: 'Trips with this station as origin or destination', type: 'integer', example: 3),
        new OA\Property(property: 'events', type: 'integer', example: 0),
        new OA\Property(property: 'eventSuggestions', type: 'integer', example: 0),
        new OA\Property(property: 'identifiers', type: 'integer', example: 2),
        new OA\Property(property: 'routeSegments', description: 'Route segments starting or ending at this station', type: 'integer', example: 4),
        new OA\Property(property: 'homeUsers', description: 'Users with this station as home station', type: 'integer', example: 1),
    ],
    type: 'object'
)]
readonly class StationUsageDto
{
    public function __construct(
        public int $stopovers,
        public int $trips,
        public int $events,
        public int $eventSuggestions,
        public int $identifiers,
        public int $routeSegments,
        public int $homeUsers,
    ) {}

    public function isInUse(): bool
    {
        return $this->stopovers + $this->trips + $this->events + $this->eventSuggestions
               + $this->identifiers + $this->routeSegments + $this->homeUsers > 0;
    }
}
