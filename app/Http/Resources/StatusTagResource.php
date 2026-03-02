<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StatusTagResource',
    title: 'StatusTagResource',
    required: ['key', 'value', 'visibility'],
    properties: [
        new OA\Property(property: 'key', type: 'string', example: 'trwl:vehicle_number'),
        new OA\Property(property: 'value', type: 'string', example: '94 80 0450 921 D-AVG'),
        new OA\Property(property: 'visibility', type: 'integer', example: '1'),
    ],
    type: 'object',
)]
class StatusTagResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'visibility' => $this->visibility->value,
        ];
    }
}
