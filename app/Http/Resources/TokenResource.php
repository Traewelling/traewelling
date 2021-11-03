<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array {
        return [
            'id'        => $this->id,
            'client'    => $this->client->name,
            'scopes'    => $this->scopes,
            'createdAt' => $this->created_at->toIso8601String(),
            'expiresAt' => $this->expires_at?->toIso8601String()
        ];
    }
}
