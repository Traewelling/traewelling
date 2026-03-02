<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TokenResource',
    required: ['id', 'client', 'scopes', 'createdAt', 'expiresAt'],
    properties: [
        new OA\Property(property: 'id', description: 'The token ID', type: 'string', example: 'abc123'),
        new OA\Property(property: 'client', description: 'The name of the client associated with the token', type: 'string', example: 'MyApp'),
        new OA\Property(property: 'scopes', description: 'The scopes associated with the token', type: 'array', items: new OA\Items(type: 'string'), example: ['read', 'write']),
        new OA\Property(property: 'createdAt', description: 'The timestamp when the token was created in ISO 8601 format', type: 'string', format: 'date-time', example: '2024-06-01T12:34:56Z'),
        new OA\Property(property: 'expiresAt', description: 'The timestamp when the token expires in ISO 8601 format', type: 'string', format: 'date-time', example: '2024-07-01T12:34:56Z'),
    ]
)]
class TokenResource extends JsonResource
{
    private User $user;

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->client->name,
            'scopes' => $this->scopes,
            'createdAt' => Carbon::parse($this->created_at)?->toIso8601String(),
            'expiresAt' => Carbon::parse($this->expires_at)?->toIso8601String(),
        ];
    }
}
