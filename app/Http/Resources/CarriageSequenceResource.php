<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarriageSequenceResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'position'       => $this->position,
            'sequence'       => $this->sequence,
            'vehicle_type'   => $this->vehicle_type,
            'vehicle_number' => $this->vehicle_number,
            'order_number'   => $this->order_number,
        ];
    }
}
