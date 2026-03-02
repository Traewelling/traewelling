<?php

namespace App\Http\Resources;

use App\Models\StationIdentifier;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StationIdentifier',
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'de_db_ril100'),
        new OA\Property(property: 'identifier', type: 'string', example: 'RK'),
    ],
)]
class StationIdentifierResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var StationIdentifier $this */
        return [
            'type' => $this->type,
            'identifier' => $this->identifier,
        ];
    }
}
