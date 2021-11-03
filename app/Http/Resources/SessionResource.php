<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
        $ip = explode('.', $this->ip_address);
        return [
            'id'           => $this->id,
            'ip'           => $ip[0] . '.' . $ip[1] . '.***.***',
            'userAgent'    => $this->user_agent,
            'platform'     => $this->platform,
            'deviceIcon'   => $this->device_icon,
            'lastActivity' => Carbon::createFromTimestamp($this->last_activity)->toIso8601String(),
        ];
    }
}
