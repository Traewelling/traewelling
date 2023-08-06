<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StopoverResource extends JsonResource
{

    public function toArray($request): array {
        return [
            'type'                     => 'stopover',
            'id'                       => (int) $this->train_station_id, //TODO: should be renamed to "stop" as of FPTF and be a stop object
            'name'                     => $this->trainStation->name,
            'rilIdentifier'            => $this->trainStation->rilIdentifier ?? null,
            'evaIdentifier'            => $this->trainStation->ibnr,
            'arrival'                  => $this->arrival?->toIso8601String(),
            'arrivalPlanned'           => $this->arrival_planned?->toIso8601String(),
            'arrivalReal'              => $this->arrival_real?->toIso8601String(),
            'arrivalPlatformPlanned'   => $this->arrival_platform_planned ?? null,
            'arrivalPlatformReal'      => $this->arrival_platform_real ?? null,
            'departure'                => $this->departure?->toIso8601String(),
            'departurePlanned'         => $this->departure_planned?->toIso8601String(),
            'departureReal'            => $this->departure_real?->toIso8601String(),
            'departurePlatformPlanned' => $this->departure_platform_planned ?? null,
            'departurePlatformReal'    => $this->departure_platform_real ?? null,
            'platform'                 => $this->platform ?? null,
            'isArrivalDelayed'         => (bool) $this->isArrivalDelayed,
            'isDepartureDelayed'       => (bool) $this->isDepartureDelayed,
            'cancelled'                => (bool) ($this->cancelled ?? false),
        ];
    }
}
