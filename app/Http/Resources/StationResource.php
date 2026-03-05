<?php

namespace App\Http\Resources;

use App\Models\Station;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Station',
    required: ['id', 'name', 'latitude', 'longitude', 'ibnr', 'rilIdentifier', 'areas'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: '1'),
        new OA\Property(property: 'name', type: 'string', example: 'Karlsruhe Hbf'),
        new OA\Property(property: 'latitude', type: 'number', example: '48.993207'),
        new OA\Property(property: 'longitude', type: 'number', example: '8.400977'),
        new OA\Property(
            property: 'ibnr',
            description: 'Deprecated. Always null. Use identifiers with type "de_db_ibnr" instead.',
            type: 'integer',
            example: null,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'rilIdentifier',
            description: 'Deprecated. Always null. Use identifiers with type "de_db_ril100" instead.',
            type: 'string',
            example: null,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'areas',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AreaResource'),
        ),
        new OA\Property(
            property: 'identifiers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/StationIdentifierResource'),
        ),
    ],
)]
class StationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Station $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ibnr' => null, // @deprecated - see identifiers
            'rilIdentifier' => null, // @deprecated - see identifiers
            'areas' => AreaResource::collection($this->whenLoaded('areas')),
            'identifiers' => StationIdentifierResource::collection($this->whenLoaded('stationIdentifiers')),
        ];
    }
}
