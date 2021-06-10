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
     * @return array
     */
    public function toArray($request): array {
        return [
            "id"          => $this->id,
            "category"    => $this->category,
            "number"      => $this->number,
            "lineName"    => $this->linename,
            "origin"      => new TrainstationResource($this->originStation),
            "destination" => new TrainstationResource($this->destinationStation),
            "stopovers"   => StopoverResource::collection($this->stopoversNEW->sortBy('departure'))
        ];
    }
}
