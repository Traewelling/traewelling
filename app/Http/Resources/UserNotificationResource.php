<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{

    public function toArray($request): array {
        return [
            'id'                 => (string) $this->id,
            'type'               => (string) str_replace('App\\Notifications\\', '', $this->type),
            'leadFormatted'      => $this->resource->type::getLead($this->resource->data),
            'lead'               => strip_tags($this->resource->type::getLead($this->resource->data)),
            'noticeFormatted'    => $this->resource->type::getNotice($this->resource->data),
            'notice'             => strip_tags($this->resource->type::getNotice($this->resource->data)),
            'link'               => $this->resource->type::getLink($this->resource->data),
            'data'               => $this->data,
            'readAt'             => $this->read_at?->toIso8601String(),
            'createdAt'          => $this->created_at->toIso8601String(),
            'createdAtForHumans' => $this->created_at->diffForHumans(),
        ];
    }
}
