<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
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
            "id"        => (string) $this->id,
            "type"      => (string) $this->type,
            "data"      => $this->data,
            "detail"    => $this->detail ?? $this->type::detail($this->fresh()),
            "readAt"    => $this?->read_at?->toIso8601String(),
            "createdAt" => $this->created_at->toIso8601String()
        ];
    }
}
