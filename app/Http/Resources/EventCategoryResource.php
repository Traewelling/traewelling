<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventCategoryResource extends JsonResource
{

    public function toArray($request): array {
        return [
            'id'   => $this->category->value,
            'name' => __('events.category.' . $this->category->value),
        ];
    }
}
