<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\Internal\Departure;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Departure',
    description: 'A single departure at a station',
    required: ['tripId', 'when', 'plannedWhen', 'platform', 'plannedPlatform', 'direction', 'line', 'cancelled', 'station'],
    properties: [
        new OA\Property(property: 'tripId', description: 'Unique trip identifier', type: 'string', example: '1|200513|0|81|6012023'),
        new OA\Property(property: 'when', description: 'Actual departure time (null if no realtime data)', type: 'string', format: 'date-time', example: '2023-01-06T13:49:00+01:00', nullable: true),
        new OA\Property(property: 'plannedWhen', description: 'Scheduled departure time', type: 'string', format: 'date-time', example: '2023-01-06T13:49:00+01:00'),
        new OA\Property(property: 'platform', description: 'Actual platform (null if no realtime data)', type: 'string', example: '3a', nullable: true),
        new OA\Property(property: 'plannedPlatform', description: 'Scheduled platform', type: 'string', example: '3', nullable: true),
        new OA\Property(property: 'direction', description: 'Final destination of the trip', type: 'string', example: 'Zürich HB'),
        new OA\Property(
            property: 'line',
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'line'),
                new OA\Property(property: 'id', type: 'string', example: 'EC 9'),
                new OA\Property(property: 'fahrtNr', description: 'Journey number', type: 'string', example: '9'),
                new OA\Property(property: 'name', type: 'string', example: 'EC 9'),
                new OA\Property(property: 'color', description: 'Route color as hex (without #)', type: 'string', example: 'ff2e3e', nullable: true),
                new OA\Property(property: 'textColor', description: 'Route text color as hex (without #)', type: 'string', example: 'ffffff', nullable: true),
                new OA\Property(property: 'mode', description: 'Transit mode', type: 'string', example: 'TRAIN', nullable: true),
                new OA\Property(property: 'product', description: 'Product category', type: 'string', example: 'national', nullable: true),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'cancelled', description: 'Whether this departure is cancelled', type: 'boolean', example: false),
        new OA\Property(property: 'station', ref: '#/components/schemas/StationResource'),
    ],
)]
class DepartureResource extends JsonResource
{
    /** @var Departure */
    public $resource;

    public function toArray($request): array
    {
        return [
            'tripId' => $this->resource->trip->tripId,
            'when' => $this->resource->realDeparture?->toIso8601String(),
            'plannedWhen' => $this->resource->plannedDeparture->toIso8601String(),
            'platform' => $this->resource->realPlatform,
            'plannedPlatform' => $this->resource->plannedPlatform,
            'direction' => $this->resource->trip->direction,
            'line' => [
                'type' => 'line',
                'id' => $this->resource->trip->lineName,
                'fahrtNr' => $this->resource->trip->number,
                'name' => $this->resource->trip->lineName,
                'color' => $this->resource->trip->routeColor,
                'textColor' => $this->resource->trip->routeTextColor,
                'mode' => $this->resource->trip->mode?->value,
                'product' => $this->resource->trip->category,
            ],
            'cancelled' => $this->resource->cancelled,
            'station' => new StationResource($this->resource->station),
        ];
    }
}
