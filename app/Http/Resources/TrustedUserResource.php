<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TrustedUser',
    required: ['user', 'expiresAt'],
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/LightUserResource'),
        new OA\Property(
            property: 'expiresAt',
            type: 'string',
            format: 'date-time',
            example: '2024-07-28T00:00:00Z',
            nullable: true,
        ),
    ],
)]
class TrustedUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user' => new LightUserResource($this->trusted),
            'expiresAt' => $this->expires_at?->toIso8601String(),
        ];
    }
}
