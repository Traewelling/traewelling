<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JourneyResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'type' => 'journey',
            'id'   => $this->id,
            'legs' => null, //TODO
        ];
    }
}
