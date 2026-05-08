<?php

namespace App\Http\Resources;

use App\Models\StationIdentifier;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StationIdentifier',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'type', type: 'string', example: 'de_db_ril100'),
        new OA\Property(property: 'identifier', type: 'string', example: 'RK'),
        new OA\Property(property: 'name', type: 'string', example: 'Karlsruhe Hbf', nullable: true),
        new OA\Property(property: 'origin', type: 'string', example: 'db', nullable: true),
        new OA\Property(property: 'latitude', type: 'number', format: 'float', example: 48.993207, nullable: true),
        new OA\Property(property: 'longitude', type: 'number', format: 'float', example: 8.400977, nullable: true),
    ],
)]
class StationIdentifierResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var StationIdentifier $this */
        return [
            'id' => $this->id,
            'type' => $this->type,
            'identifier' => $this->identifier,
            'name' => $this->name,
            'origin' => $this->origin,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
