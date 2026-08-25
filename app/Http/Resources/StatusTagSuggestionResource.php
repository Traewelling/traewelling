<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\StatusTagSuggestionDto;
use App\Enum\TagSuggestionSource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StatusTagSuggestionResource',
    title: 'StatusTagSuggestionResource',
    required: ['key', 'value', 'source'],
    properties: [
        new OA\Property(property: 'key', type: 'string', example: 'trwl:vehicle_number'),
        new OA\Property(property: 'value', type: 'string', example: '94 80 0450 921 D-AVG'),
        new OA\Property(property: 'source', ref: TagSuggestionSource::class),
    ],
    type: 'object',
)]
class StatusTagSuggestionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var StatusTagSuggestionDto $this */
        return [
            'key' => $this->key,
            'value' => $this->value,
            'source' => $this->source->value,
        ];
    }
}
