<?php

declare(strict_types=1);

namespace App\Dto\RouteMap;

use App\Enum\HafasTravelType;

readonly class RouteMapEntryDto
{
    /**
     * @param  string|null  $routeSegmentId  UUID of the underlying route segment, null for approximated entries.
     * @param  string|null  $fromStationUuid  Only set for approximated entries.
     * @param  string|null  $toStationUuid  Only set for approximated entries.
     * @param  HafasTravelType[]  $categories  Modes of transport this stretch was travelled with.
     * @param  bool  $approximated  True if this is a straight line between two stations
     *                              because no route segment exists yet.
     */
    public function __construct(
        public ?string $routeSegmentId,
        public ?string $fromStationUuid,
        public ?string $toStationUuid,
        public string $polyline,
        public int $polylinePrecision,
        public ?int $distance,
        public ?string $pathType,
        public array $categories,
        public bool $approximated,
    ) {}
}
