<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{

    public function toArray($request): array {
        return [
            'id'               => (string) $this->id,
            'type'             => (string) str_replace('App\\Notifications\\', '', $this->type),
            'lead_formatted'   => $this->resource->type::getLead($this->resource->data),
            'lead'             => strip_tags($this->resource->type::getLead($this->resource->data)),
            'notice_formatted' => $this->resource->type::getNotice($this->resource->data),
            'notice'           => strip_tags($this->resource->type::getNotice($this->resource->data)),
            'icon'             => $this->resource->type::getIcon($this->resource->data),
            'link'             => $this->resource->type::getLink($this->resource->data),
            'data'             => $this->data,
            'readAt'           => $this?->read_at?->toIso8601String(),
            'createdAt'        => $this->created_at->toIso8601String()
        ];
    }
}
