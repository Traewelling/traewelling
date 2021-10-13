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
            "rilIdentifier"            => $this->trainStation->rilIdentifier ?? null,
            "arrival"                  => $this->arrival?->toIso8601String(),
            "arrivalPlanned"           => $this->arrival_planned?->toIso8601String(),
            "arrivalReal"              => $this->arrival_real?->toIso8601String(),
            "arrivalPlatformPlanned"   => $this->arrival_platform_planned,
            "arrivalPlatformReal"      => $this->arrival_platform_real,
            "departure"                => $this->departure?->toIso8601String(),
            "departurePlanned"         => $this->departure_planned?->toIso8601String(),
            "departureReal"            => $this->departure_real?->toIso8601String(),
            "departurePlatformPlanned" => $this->departure_platform_planned,
            "departurePlatformReal"    => $this->departure_platform_real,
            "platform"                 => $this->platform,
            "isArrivalDelayed"         => (bool) $this->isArrivalDelayed,
            "isDepartureDelayed"       => (bool) $this->isDepartureDelayed
        ];
    }
}
