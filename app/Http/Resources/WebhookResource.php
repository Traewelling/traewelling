<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'      => $this->external_id,
            'user_id' => $this->user_id,
            'url'     => $this->url,
        ];
    }
}
