<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StopoverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array {
        return [
            "id"                       => (int) $this->train_station_id,
            "name"                     => $this->trainStation->name,
            "arrival"                  => $this->arrival,
            "arrivalPlanned"           => $this->arrival_planned,
            "arrivalReal"              => $this->arrival_real,
            "arrivalPlatformPlanned"   => $this->arrival_platform_planned,
            "arrivalPlatformReal"      => $this->arrival_platform_real,
            "departure"                => $this->departure,
            "departurePlanned"         => $this->departure_planned,
            "departureReal"            => $this->departure_real,
            "departurePlatformPlanned" => $this->departure_platform_planned,
            "departurePlatformReal"    => $this->departure_platform_real,
            "platform"                 => $this->platform,
            "isArrivalDelayed"         => (bool) $this->isArrivalDelayed,
            "isDepartureDelayed"       => (bool) $this->isDepartureDelayed
        ];
    }
}
