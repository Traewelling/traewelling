<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\Internal\Departure;
use App\Enum\StationIdentifierType;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Departure',
    description: 'A single departure at a station',
    required: ['tripId', 'stop', 'when', 'plannedWhen', 'delay', 'platform', 'plannedPlatform', 'direction', 'line', 'station'],
    properties: [
        new OA\Property(property: 'tripId', description: 'Unique trip identifier', type: 'string', example: '1|200513|0|81|6012023'),
        new OA\Property(
            property: 'stop',
            description: 'The stop at which this departure occurs',
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'stop', deprecated: true),
                new OA\Property(property: 'id', description: 'Träwelling internal station ID', type: 'integer', example: 5181, deprecated: true),
                new OA\Property(property: 'name', type: 'string', example: 'Karlsruhe Hbf', deprecated: true),
                new OA\Property(
                    property: 'location',
                    properties: [
                        new OA\Property(property: 'type', type: 'string', example: 'location', deprecated: true),
                        new OA\Property(property: 'id', description: 'IBNR identifier (if available)', type: 'string', example: '8000191', nullable: true, deprecated: true),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 48.993207, deprecated: true),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 8.400977, deprecated: true),
                    ],
                    type: 'object',
                    deprecated: true,
                ),
                new OA\Property(
                    property: 'products',
                    description: 'Deprecated. Always true for all modes.',
                    type: 'object',
                    deprecated: true,
                ),
            ],
            type: 'object',
            deprecated: true,
        ),
        new OA\Property(property: 'when', description: 'Actual departure time (null if no realtime data)', type: 'string', format: 'date-time', example: '2023-01-06T13:49:00+01:00', nullable: true),
        new OA\Property(property: 'plannedWhen', description: 'Scheduled departure time', type: 'string', format: 'date-time', example: '2023-01-06T13:49:00+01:00'),
        new OA\Property(property: 'delay', description: 'Deprecated. Use the difference between when and plannedWhen instead.', type: 'integer', example: 2, nullable: true, deprecated: true),
        new OA\Property(property: 'platform', description: 'Actual platform (null if no realtime data)', type: 'string', example: '3a', nullable: true),
        new OA\Property(property: 'plannedPlatform', description: 'Scheduled platform', type: 'string', example: '3', nullable: true),
        new OA\Property(property: 'direction', description: 'Final destination of the trip', type: 'string', example: 'Zürich HB'),
        new OA\Property(property: 'provenance', description: 'Deprecated. Always null.', type: 'string', example: null, nullable: true, deprecated: true),
        new OA\Property(
            property: 'line',
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'line'),
                new OA\Property(property: 'id', type: 'string', example: 'EC 9'),
                new OA\Property(property: 'fahrtNr', description: 'Journey number', type: 'string', example: '9'),
                new OA\Property(property: 'name', type: 'string', example: 'EC 9'),
                new OA\Property(property: 'color', description: 'Route color as hex (without #)', type: 'string', example: 'ff2e3e', nullable: true),
                new OA\Property(property: 'textColor', description: 'Route text color as hex (without #)', type: 'string', example: 'ffffff', nullable: true),
                new OA\Property(property: 'public', description: 'Deprecated. Always true.', type: 'boolean', example: true, deprecated: true),
                new OA\Property(property: 'productName', description: 'Deprecated. Use name.', type: 'string', example: 'EC 9', deprecated: true),
                new OA\Property(property: 'mode', description: 'Transit mode', type: 'string', example: 'TRAIN', nullable: true),
                new OA\Property(property: 'product', description: 'Product category', type: 'string', example: 'national', nullable: true),
                new OA\Property(property: 'adminCode', description: 'Deprecated. Always "80____".', type: 'string', example: '80____', deprecated: true),
                new OA\Property(property: 'operator', description: 'Deprecated. Always null.', type: 'object', nullable: true, deprecated: true),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'remarks', description: 'Deprecated. Always null.', type: 'array', items: new OA\Items(), nullable: true, deprecated: true),
        new OA\Property(property: 'origin', description: 'Deprecated. Always null.', type: 'object', nullable: true, deprecated: true),
        new OA\Property(
            property: 'destination',
            description: 'Deprecated. Use direction instead.',
            type: 'object',
            deprecated: true,
        ),
        new OA\Property(property: 'currentTripPosition', description: 'Deprecated. Always null.', type: 'object', nullable: true, deprecated: true),
        new OA\Property(property: 'loadFactor', description: 'Deprecated. Always null.', type: 'string', nullable: true, deprecated: true),
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
            'stop' => [ // @deprecated - remove after 2026-09-30, use station instead
                'type' => 'stop',
                'id' => $this->resource->station->id,
                'name' => $this->resource->station->name,
                'location' => [
                    'type' => 'location',
                    'id' => $this->resource->station->getIdentifier(StationIdentifierType::DE_DB_IBNR)?->identifier,
                    'latitude' => $this->resource->station->latitude,
                    'longitude' => $this->resource->station->longitude,
                ],
                'products' => $this->placeholderProducts(), // @deprecated - remove after 2026-09-30
            ],
            'when' => $this->resource->realDeparture?->toIso8601String(),
            'plannedWhen' => $this->resource->plannedDeparture->toIso8601String(),
            'delay' => $this->resource->getDelay(), // @deprecated - remove after 2026-09-30, use when/plannedWhen difference instead
            'platform' => $this->resource->realPlatform,
            'plannedPlatform' => $this->resource->plannedPlatform,
            'direction' => $this->resource->trip->direction,
            'provenance' => null, // @deprecated - remove after 2026-09-30
            'line' => [
                'type' => 'line',
                'id' => $this->resource->trip->lineName,
                'fahrtNr' => $this->resource->trip->number,
                'name' => $this->resource->trip->lineName,
                'color' => $this->resource->trip->routeColor,
                'textColor' => $this->resource->trip->routeTextColor,
                'public' => true, // @deprecated - remove after 2026-09-30
                'adminCode' => '80____', // @deprecated - remove after 2026-09-30
                'productName' => $this->resource->trip->lineName, // @deprecated - remove after 2026-09-30, use name instead
                'mode' => $this->resource->trip->mode?->value,
                'product' => $this->resource->trip->category,
                'operator' => null, // @deprecated - remove after 2026-09-30
            ],
            'remarks' => null, // @deprecated - remove after 2026-09-30
            'origin' => null, // @deprecated - remove after 2026-09-30
            'destination' => [ // @deprecated - remove after 2026-09-30, use direction instead
                'type' => 'stop',
                'id' => 0,
                'name' => $this->resource->trip->direction,
                'location' => [
                    'type' => 'location',
                    'id' => 0,
                    'latitude' => 0,
                    'longitude' => 0,
                ],
                'products' => $this->placeholderProducts(),
            ],
            'currentTripPosition' => null, // @deprecated - remove after 2026-09-30
            'loadFactor' => null, // @deprecated - remove after 2026-09-30
            'cancelled' => $this->resource->cancelled,
            'station' => new StationResource($this->resource->station),
        ];
    }

    /**
     * @return array<string, bool>
     *
     * @deprecated Remove after 2026-09-30, products are always true and this field is deprecated anyway.
     */
    private function placeholderProducts(): array
    {
        return [
            'nationalExpress' => true,
            'national' => true,
            'regionalExp' => true,
            'regional' => true,
            'suburban' => true,
            'bus' => true,
            'ferry' => true,
            'subway' => true,
            'tram' => true,
            'taxi' => true,
        ];
    }
}
