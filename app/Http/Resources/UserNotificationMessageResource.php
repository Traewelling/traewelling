<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationMessageResource extends JsonResource
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
            'icon'     => $this['icon'] ?? '',
            'severity' => $this['severity'] ?? 'notice',
            'lead'     => $this['lead'] ?? [],
            'notice'   => $this['notice'] ?? []
        ];
    }
}
