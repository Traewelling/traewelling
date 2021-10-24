<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserNotificationMessageResource extends JsonResource
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
            'icon'     => $this['icon'] ?? "",
            'severity' => $this['severity'] ?? "notice",
            'lead'     => $this['lead'] ?? [],
            'notice'   => $this['notice'] ?? []
        ];
    }
}
