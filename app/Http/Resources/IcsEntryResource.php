<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IcsEntryResource extends JsonResource
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
            'id'           => $this->id,
            'token'        => substr($this->token, 0, 8),
            'name'         => $this->name,
            'created'      => $this->created_at?->toIso8601String(),
            'lastAccessed' => $this->last_accessed?->toIso8601String()
        ];
    }
}
