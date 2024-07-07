<?php

namespace App\Http\Resources;

use App\Models\Stopover;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="StopoverResource",
 *     @OA\Property(property="id", type="integer", example=12345),
 *     @OA\Property(property="name", type="string", example="Karlsruhe Hbf", description="name of the station"),
 *     @OA\Property(property="rilIdentifier", type="string", example="RK", nullable=true, description="Identifier specified in 'Richtline 100' of the Deutsche Bahn"),
 *     @OA\Property(property="evaIdentifier", type="string", example="8000191", nullable=true, description="IBNR identifier of Deutsche Bahn"),
 *     @OA\Property(property="arrival", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true, description="currently known arrival time. Equal to arrivalReal if known. Else equal to arrivalPlanned."),
 *     @OA\Property(property="arrivalPlanned", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true, description="planned arrival according to timetable records"),
 *     @OA\Property(property="arrivalReal", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true, description="real arrival according to live data"),
 *     @OA\Property(property="arrivalPlatformPlanned", type="string", example="5", nullable=true, description="planned arrival platform according to timetable records"),
 *     @OA\Property(property="arrivalPlatformReal", type="string", example="5 A-F", nullable=true, description="real arrival platform according to live data"),
 *     @OA\Property(property="departure", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true, description="currently known departure time. Equal to departureReal if known. Else equal to departurePlanned."),
 *     @OA\Property(property="departurePlanned", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true, description="planned departure according to timetable records"),
 *     @OA\Property(property="departureReal", type="string", format="date-time", example="2022-07-17T13:37:00+02:00", nullable=true, description="real departure according to live data"),
 *     @OA\Property(property="departurePlatformPlanned", type="string", example="5", nullable=true, description="planned departure platform according to timetable records"),
 *     @OA\Property(property="departurePlatformReal", type="string", example="5 A-F", nullable=true, description="real departure platform according to live data"),
 *     @OA\Property(property="platform", type="string", example="5 A-F", nullable=true),
 *     @OA\Property(property="isArrivalDelayed", type="boolean", example=false, description="Is there a delay in the arrival time?"),
 *     @OA\Property(property="isDepartureDelayed", type="boolean", example=false, description="Is there a delay in the departure time?"),
 *     @OA\Property(property="cancelled", type="boolean", example=false, description="is this stopover cancelled?"),
 * )
 */
class StopoverResource extends JsonResource
{

    public function toArray($request): array {
        /** @var Stopover $this */
        return [
            'id'                       => (int) $this->train_station_id,
            'name'                     => $this->station->name,
            'rilIdentifier'            => $this->station->rilIdentifier ?? null,
            'evaIdentifier'            => $this->station->ibnr ?? null,
            'arrival'                  => $this->arrival?->toIso8601String(), //TODO: not necessary if planned and real are available
            'arrivalPlanned'           => $this->arrival_planned?->toIso8601String(),
            'arrivalReal'              => $this->arrival_real?->toIso8601String(),
            'arrivalPlatformPlanned'   => $this->arrival_platform_planned ?? null,
            'arrivalPlatformReal'      => $this->arrival_platform_real ?? null,
            'departure'                => $this->departure?->toIso8601String(), //TODO: not necessary if planned and real are available
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
