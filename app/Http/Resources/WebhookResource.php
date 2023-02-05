<?php

namespace App\Http\Resources;

use App\Enum\WebhookEvent;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'clientId' => $this->oauth_client_id,
            'userId' => $this->user_id,
            'url' => $this->url,
            'createdAt' => $this->created_at->toIso8601String(),
            'events' => array_map(function ($event) {
                return WebhookEvent::from($event)->name();
            }, $this->events),
        ];
    }
}
