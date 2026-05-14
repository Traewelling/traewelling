<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StatusTagSuggestionResource',
    title: 'StatusTagSuggestionResource',
    required: ['key', 'value'],
    properties: [
        new OA\Property(property: 'key', type: 'string', example: 'trwl:vehicle_number'),
        new OA\Property(property: 'value', type: 'string', example: '94 80 0450 921 D-AVG'),
    ],
    type: 'object',
)]
class StatusTagSuggestionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
}
