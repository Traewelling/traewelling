<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventDetailsResource extends JsonResource
{

    public function toArray($request): array {
        return [
            "id"            => $this->id,
            "slug"          => $this->slug,
            "trainDistance" => $this->totalDistance, // @todo: rename key - we have more than just trains
            "trainDuration" => $this->totalDuration, // @todo: rename key - we have more than just trains
        ];
    }
}
