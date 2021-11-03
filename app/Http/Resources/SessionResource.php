<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
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
            'ip'           => $this->ip_address,
            'userAgent'    => $this->user_agent,
            'platform'     => $this->platform,
            'deviceIcon'   => $this->device_icon,
            'lastActivity' => $this->last_activity?->toIso8601String(),
        ];
    }
}
