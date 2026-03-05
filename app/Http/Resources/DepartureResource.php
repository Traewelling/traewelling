<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\Internal\Departure;
use App\StationIdentifierType;
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
                new OA\Property(property: 'type', type: 'string', example: 'stop'),
                new OA\Property(property: 'id', description: 'Träwelling internal station ID', type: 'integer', example: 5181),
                new OA\Property(property: 'name', type: 'string', example: 'Karlsruhe Hbf'),
                new OA\Property(
                    property: 'location',
                    properties: [
                        new OA\Property(property: 'type', type: 'string', example: 'location'),
                        new OA\Property(property: 'id', description: 'IBNR identifier (if available)', type: 'string', example: '8000191', nullable: true),
                        new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 48.993207),
                        new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 8.400977),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'products',
                    description: 'Deprecated. Always true for all modes.',
                    type: 'object',
                    deprecated: true,
                ),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'when', description: 'Actual departure time (null if no realtime data)', type: 'string', format: 'date-time', example: '2023-01-06T13:49:00+01:00', nullable: true),
        new OA\Property(property: 'plannedWhen', description: 'Scheduled departure time', type: 'string', format: 'date-time', example: '2023-01-06T13:49:00+01:00'),
        new OA\Property(property: 'delay', description: 'Delay in minutes (null if no realtime data). Deprecated, use when/plannedWhen difference.', type: 'integer', example: 2, nullable: true, deprecated: true),
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
                new OA\Property(property: 'product', description: 'Deprecated HAFAS product category', type: 'string', example: 'national', nullable: true, deprecated: true),
                new OA\Property(property: 'adminCode', description: 'Deprecated. Always "80____".', type: 'string', example: '80____', deprecated: true),
                new OA\Property(property: 'operator', description: 'Deprecated. Always null.', type: 'object', nullable: true, deprecated: true),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'remarks', description: 'Deprecated. Always null.', type: 'array', items: new OA\Items(), nullable: true, deprecated: true),
        new OA\Property(property: 'origin', description: 'Deprecated. Always null.', type: 'object', nullable: true, deprecated: true),
        new OA\Property(
            property: 'destination',
            description: 'Destination stop. Only name is currently populated; all other fields are deprecated placeholders.',
            properties: [
                new OA\Property(property: 'type', type: 'string', example: 'stop'),
                new OA\Property(property: 'id', description: 'Deprecated. Always 0.', type: 'integer', example: 0, deprecated: true),
                new OA\Property(property: 'name', description: 'Final destination name', type: 'string', example: 'Zürich HB'),
                new OA\Property(
                    property: 'location',
                    description: 'Deprecated. All values are always 0.',
                    properties: [
                        new OA\Property(property: 'type', type: 'string', example: 'location'),
                        new OA\Property(property: 'id', description: 'Deprecated. Always 0.', type: 'integer', example: 0, deprecated: true),
                        new OA\Property(property: 'latitude', description: 'Deprecated. Always 0.', type: 'number', format: 'float', example: 0, deprecated: true),
                        new OA\Property(property: 'longitude', description: 'Deprecated. Always 0.', type: 'number', format: 'float', example: 0, deprecated: true),
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
        ),
        new OA\Property(property: 'currentTripPosition', description: 'Deprecated. Always null.', type: 'object', nullable: true, deprecated: true),
        new OA\Property(property: 'loadFactor', description: 'Deprecated. Always null.', type: 'string', nullable: true, deprecated: true),
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
            'stop' => [
                'type' => 'stop',
                'id' => $this->resource->station->id,
                'name' => $this->resource->station->name,
                'location' => [
                    'type' => 'location',
                    'id' => $this->resource->station->getIdentifier(StationIdentifierType::DE_DB_IBNR)?->identifier,
                    'latitude' => $this->resource->station->latitude,
                    'longitude' => $this->resource->station->longitude,
                ],
                'products' => $this->placeholderProducts(), // deprecated in documentation
            ],
            'when' => $this->resource->realDeparture?->toIso8601String(),
            'plannedWhen' => $this->resource->plannedDeparture->toIso8601String(),
            'delay' => $this->resource->getDelay(), // deprecated in documentation
            'platform' => $this->resource->realPlatform,
            'plannedPlatform' => $this->resource->plannedPlatform,
            'direction' => $this->resource->trip->direction,
            'provenance' => null, // deprecated in documentation
            'line' => [
                'type' => 'line',
                'id' => $this->resource->trip->lineName,
                'fahrtNr' => $this->resource->trip->number,
                'name' => $this->resource->trip->lineName,
                'color' => $this->resource->trip->routeColor,
                'textColor' => $this->resource->trip->routeTextColor,
                'public' => true, // deprecated in documentation
                'adminCode' => '80____', // deprecated in documentation
                'productName' => $this->resource->trip->lineName, // deprecated in documentation
                'mode' => $this->resource->trip->mode?->value,
                'product' => $this->resource->trip->category, // deprecated in documentation
                'operator' => null, // deprecated in documentation
            ],
            'remarks' => null, // deprecated in documentation
            'origin' => null, // deprecated in documentation
            'destination' => [
                'type' => 'stop',
                'id' => 0, // deprecated in documentation
                'name' => $this->resource->trip->direction,
                'location' => [ // deprecated in documentation
                    'type' => 'location',
                    'id' => 0, // deprecated in documentation
                    'latitude' => 0, // deprecated in documentation
                    'longitude' => 0, // deprecated in documentation
                ],
                'products' => $this->placeholderProducts(), // deprecated in documentation
            ],
            'currentTripPosition' => null, // deprecated in documentation
            'loadFactor' => null, // deprecated in documentation
            'station' => new StationResource($this->resource->station),
        ];
    }

    /** @return array<string, bool> */
    private function placeholderProducts(): array
    {
        // TODO: populate from actual transit data
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
