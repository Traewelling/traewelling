<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request) {
        return [
            "id"        => (string) $this->id,
            "type"      => (string) $this->type,
            "data"      => $this->data,
            "detail"    => $this->detail ?? $this->type::detail($this->fresh()),
            "readAt"    => $this?->read_at,
            "createdAt" => $this->created_at
        ];
    }
}
