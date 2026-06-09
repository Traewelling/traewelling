<?php

namespace App\Http\Resources;

use App\Enum\StatusTagKey;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'StatusTagResource',
    title: 'StatusTagResource',
    required: ['key', 'value', 'visibility'],
    properties: [
        new OA\Property(property: 'key', oneOf: [
            new OA\Schema(ref: StatusTagKey::class, type: 'string'),
            new OA\Schema(type: 'string', example: 'custom_tag', description: 'regex:/^\w[^\/\n\r%?\\<>]*$/'),
        ]),
        new OA\Property(property: 'value', oneOf: [
            new OA\Schema(description: 'Values allowed for the tag trwl:social_status', type: 'string', enum: ['open', 'open_find_me', 'open_lets_hang', 'do_not_disturb']),
            new OA\Schema(type: 'string', example: '94 80 0450 921 D-AVG'),
        ]),
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
