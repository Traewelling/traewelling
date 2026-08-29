<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\RouteMap\RouteMapEntryDto;
use App\Enum\HafasTravelType;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'RouteMapEntryResource',
    description: 'One stretch of the network the user has travelled, deduplicated over all check-ins.',
    required: ['routeSegmentId', 'fromStation', 'toStation', 'polyline', 'polylinePrecision', 'distance', 'pathType', 'categories', 'approximated'],
    properties: [
        new OA\Property(
            property: 'routeSegmentId',
            description: 'UUID of the underlying route segment. Null when the stretch is approximated.',
            type: 'string',
            format: 'uuid',
            nullable: true,
        ),
        new OA\Property(
            property: 'fromStation',
            description: 'UUID of the station the stretch starts at. Only set for approximated stretches.',
            type: 'string',
            format: 'uuid',
            nullable: true,
        ),
        new OA\Property(
            property: 'toStation',
            description: 'UUID of the station the stretch ends at. Only set for approximated stretches.',
            type: 'string',
            format: 'uuid',
            nullable: true,
        ),
        new OA\Property(property: 'polyline', description: 'Google Encoded Polyline', type: 'string', example: '_p~iF~ps|U_ulLnnqC_mqNvxq`@'),
        new OA\Property(property: 'polylinePrecision', type: 'integer', example: 5),
        new OA\Property(property: 'distance', description: 'Length of the stretch in meters', type: 'integer', example: 42300, nullable: true),
        new OA\Property(property: 'pathType', description: 'How the stretch was routed', type: 'string', example: 'rail', nullable: true),
        new OA\Property(
            property: 'categories',
            description: 'Modes of transport this stretch was travelled with.',
            type: 'array',
            items: new OA\Items(ref: HafasTravelType::class),
        ),
        new OA\Property(
            property: 'approximated',
            description: 'True when no route segment exists yet and the stretch is a straight line between both stations.',
            type: 'boolean',
            example: false,
        ),
    ],
)]
class RouteMapEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var RouteMapEntryDto $entry */
        $entry = $this->resource;

        return [
            'routeSegmentId' => $entry->routeSegmentId,
            'fromStation' => $entry->fromStationUuid,
            'toStation' => $entry->toStationUuid,
            'polyline' => $entry->polyline,
            'polylinePrecision' => $entry->polylinePrecision,
            'distance' => $entry->distance,
            'pathType' => $entry->pathType,
            'categories' => array_map(static fn (HafasTravelType $type) => $type->value, $entry->categories),
            'approximated' => $entry->approximated,
        ];
    }
}
