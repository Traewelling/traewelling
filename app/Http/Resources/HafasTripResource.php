<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HafasTripResource extends JsonResource
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
            'id'            => $this->id,
            'category'      => $this->category->value,
            'number'        => $this->number,
            'lineName'      => $this->linename,
            'journeyNumber' => $this->journey_number,
            'origin'        => new StationResource($this->originStation),
            'destination'   => new StationResource($this->destinationStation),
            'stopovers'     => StopoverResource::collection($this->stopovers)
        ];
    }
}
