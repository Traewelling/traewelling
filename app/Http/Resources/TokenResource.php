<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TokenResource',
    required: ['id', 'client', 'scopes', 'createdAt', 'expiresAt', 'refreshExpiresAt'],
    properties: [
        new OA\Property(property: 'id', description: 'The token ID', type: 'string', example: 'abc123'),
        new OA\Property(property: 'client', description: 'The name of the client associated with the token', type: 'string', example: 'MyApp'),
        new OA\Property(property: 'scopes', description: 'The scopes associated with the token', type: 'array', items: new OA\Items(type: 'string'), example: ['read', 'write']),
        new OA\Property(property: 'createdAt', description: 'The timestamp when the token was created in ISO 8601 format', type: 'string', format: 'date-time', example: '2024-06-01T12:34:56Z'),
        new OA\Property(property: 'expiresAt', description: 'The timestamp when the token expires in ISO 8601 format', type: 'string', format: 'date-time', example: '2024-07-01T12:34:56Z'),
        new OA\Property(property: 'refreshExpiresAt', description: 'The timestamp until which the client can renew this token through its refresh token, in ISO 8601 format. Null when no valid refresh token exists, in which case access ends with expiresAt.', type: 'string', format: 'date-time', example: '2024-07-31T12:34:56Z', nullable: true),
    ]
)]
class TokenResource extends JsonResource
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
            'client' => $this->client->name,
            'scopes' => $this->scopes,
            'createdAt' => Carbon::parse($this->created_at)?->toIso8601String(),
            'expiresAt' => Carbon::parse($this->expires_at)?->toIso8601String(),
            'refreshExpiresAt' => $this->validRefreshTokenExpiry()?->toIso8601String(),
        ];
    }

    private function validRefreshTokenExpiry(): ?Carbon
    {
        $refreshToken = $this->refreshToken;

        if ($refreshToken === null || $refreshToken->revoked || $refreshToken->expires_at === null) {
            return null;
        }

        return $refreshToken->expires_at->isFuture() ? $refreshToken->expires_at : null;
    }
}
