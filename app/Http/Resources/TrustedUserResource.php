<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="TrustedUser",
 *     @OA\Property(property="user", ref="#/components/schemas/LightUser"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", example="2024-07-28T00:00:00Z")
 * )
 */
class TrustedUserResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'user'       => new LightUserResource($this->trusted),
            'expires_at' => $this->expires_at?->toIso8601String()
        ];
    }
}
