<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    required: ['id', 'token', 'name', 'createdAt', 'lastAccessed'],
    type: 'object',
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The unique identifier of the ICS token',
            type: 'integer',
            example: 1,
        ),
        new OA\Property(
            property: 'token',
            description: 'The first 8 characters of the ICS token',
            type: 'string',
            example: 'abcd1234',
        ),
        new OA\Property(
            property: 'name',
            description: 'The name of the ICS token',
            type: 'string',
            example: 'My ICS Token',
        ),
        new OA\Property(
            property: 'createdAt',
            description: 'The ISO 8601 timestamp when the ICS token was created',
            type: 'string',
            format: 'date-time',
            example: '2024-01-01T12:00:00Z',
            nullable: true,
        ),
        new OA\Property(
            property: 'lastAccessed',
            description: 'The ISO 8601 timestamp when the ICS token was last accessed',
            type: 'string',
            format: 'date-time',
            example: '2024-01-15T08:30:00Z',
            nullable: true,
        ),
    ],
)]
class IcsEntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'token' => substr($this->token, 0, 8),
            'name' => $this->name,
            'createdAt' => $this->created_at?->toIso8601String(),
            'lastAccessed' => $this->last_accessed?->toIso8601String(),
        ];
    }
}
