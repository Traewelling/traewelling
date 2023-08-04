<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventDetailsResource extends JsonResource
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
            "id"            => $this->id,
            "slug"          => $this->slug,
            "trainDistance" => $this->trainDistance,
            "trainDuration" => $this->trainDuration,
        ];
    }
}
