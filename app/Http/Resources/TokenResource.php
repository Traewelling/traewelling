<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'createdAt' => Carbon::parse($this->created_at)?->toIso8601String(),
            'expiresAt' => Carbon::parse($this->expires_at)?->toIso8601String()
        ];
    }
}
