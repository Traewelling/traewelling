<?php

declare(strict_types=1);

namespace App\Http\Resources\Export;

use App\Models\Stopover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportStopoverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Stopover $this */
        return [
            'id' => (int) $this->train_station_id,
            'name' => $this->station->name,
            'arrivalPlanned' => $this->arrival_planned?->toIso8601String(),
            'arrivalReal' => $this->arrival_real?->toIso8601String(),
            'arrivalPlatformPlanned' => $this->arrival_platform_planned,
            'arrivalPlatformReal' => $this->arrival_platform_real,
            'departurePlanned' => $this->departure_planned?->toIso8601String(),
            'departureReal' => $this->departure_real?->toIso8601String(),
            'departurePlatformPlanned' => $this->departure_platform_planned,
            'departurePlatformReal' => $this->departure_platform_real,
            'isArrivalDelayed' => (bool) $this->isArrivalDelayed,
            'isDepartureDelayed' => (bool) $this->isDepartureDelayed,
            'cancelled' => (bool) ($this->cancelled ?? false),
        ];
    }
}
