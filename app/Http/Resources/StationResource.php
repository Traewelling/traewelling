<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StationResource extends JsonResource
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
            "id"            => (int) $this->id,
            "name"          => $this->name,
            "latitude"      => (float) $this->latitude,
            "longitude"     => (float) $this->longitude,
            "ibnr"          => (int) $this->ibnr,
            "rilIdentifier" => $this->rilIdentifier
        ];
    }
}
