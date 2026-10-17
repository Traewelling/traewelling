<?php

namespace App\Http\Resources;

use App\Models\Station;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Station',
    required: ['id', 'uuid', 'name', 'latitude', 'longitude', 'areas', 'identifiers', 'time_offset', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: '1'),
        new OA\Property(property: 'uuid', description: 'Stable identifier of this station. Will become the primary key later.', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000', nullable: true),
        new OA\Property(property: 'name', type: 'string', example: 'Karlsruhe Hbf'),
        new OA\Property(property: 'latitude', type: 'number', example: '48.993207'),
        new OA\Property(property: 'longitude', type: 'number', example: '8.400977'),
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
        new OA\Property(property: 'time_offset', type: 'integer', example: '60', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
class StationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Station $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'time_offset' => $this->time_offset,
            'areas' => AreaResource::collection($this->whenLoaded('areas')),
            'identifiers' => StationIdentifierResource::collection($this->whenLoaded('stationIdentifiers')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
