<?php

namespace App\Http\Resources;

use App\Models\Stopover;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StopoverResource',
    required: [
        'id',
        'name',
        'rilIdentifier',
        'evaIdentifier',
        'arrival',
        'arrivalPlanned',
        'arrivalReal',
        'arrivalPlatformPlanned',
        'arrivalPlatformReal',
        'departure',
        'departurePlanned',
        'departureReal',
        'departurePlatformPlanned',
        'departurePlatformReal',
        'platform',
        'isArrivalDelayed',
        'isDepartureDelayed',
        'cancelled',
    ],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12345),
        new OA\Property(
            property: 'name',
            description: 'name of the station',
            type: 'string',
            example: 'Karlsruhe Hbf',
        ),
        new OA\Property(
            property: 'rilIdentifier',
            description: 'Identifier specified in \'Richtline 100\' of the Deutsche Bahn',
            type: 'string',
            example: 'RK',
            nullable: true,
        ),
        new OA\Property(
            property: 'evaIdentifier',
            description: 'IBNR identifier of Deutsche Bahn',
            type: 'string',
            example: '8000191',
            nullable: true,
        ),
        new OA\Property(
            property: 'arrival',
            description: 'currently known arrival time. Equal to arrivalReal if known. Else equal to arrivalPlanned.',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'arrivalPlanned',
            description: 'planned arrival according to timetable records',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'arrivalReal',
            description: 'real arrival according to live data',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'arrivalPlatformPlanned',
            description: 'planned arrival platform according to timetable records',
            type: 'string',
            example: '5',
            nullable: true,
        ),
        new OA\Property(
            property: 'arrivalPlatformReal',
            description: 'real arrival platform according to live data',
            type: 'string',
            example: '5 A-F',
            nullable: true,
        ),
        new OA\Property(
            property: 'departure',
            description: 'currently known departure time. Equal to departureReal if known. Else equal to departurePlanned.',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'departurePlanned',
            description: 'planned departure according to timetable records',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'departureReal',
            description: 'real departure according to live data',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'departurePlatformPlanned',
            description: 'planned departure platform according to timetable records',
            type: 'string',
            example: '5',
            nullable: true,
        ),
        new OA\Property(
            property: 'departurePlatformReal',
            description: 'real departure platform according to live data',
            type: 'string',
            example: '5 A-F',
            nullable: true,
        ),
        new OA\Property(property: 'platform', type: 'string', example: '5 A-F', nullable: true),
        new OA\Property(
            property: 'isArrivalDelayed',
            description: 'Is there a delay in the arrival time?',
            type: 'boolean',
            example: false,
        ),
        new OA\Property(
            property: 'isDepartureDelayed',
            description: 'Is there a delay in the departure time?',
            type: 'boolean',
            example: false,
        ),
        new OA\Property(
            property: 'cancelled',
            description: 'is this stopover cancelled?',
            type: 'boolean',
            example: false,
        ),
    ],
)]
class StopoverResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Stopover $this */
        return [
            'id' => (int) $this->train_station_id,
            'name' => $this->station->name,
            'rilIdentifier' => $this->station->rilIdentifier ?? null,
            'evaIdentifier' => $this->station->ibnr ?? null,
            'arrival' => $this->arrival?->toIso8601String(), // TODO: not necessary if planned and real are available
            'arrivalPlanned' => $this->arrival_planned?->toIso8601String(),
            'arrivalReal' => $this->arrival_real?->toIso8601String(),
            'arrivalPlatformPlanned' => $this->arrival_platform_planned ?? null,
            'arrivalPlatformReal' => $this->arrival_platform_real ?? null,
            'departure' => $this->departure?->toIso8601String(), // TODO: not necessary if planned and real are available
            'departurePlanned' => $this->departure_planned?->toIso8601String(),
            'departureReal' => $this->departure_real?->toIso8601String(),
            'departurePlatformPlanned' => $this->departure_platform_planned ?? null,
            'departurePlatformReal' => $this->departure_platform_real ?? null,
            'platform' => $this->platform ?? null,
            'isArrivalDelayed' => (bool) $this->isArrivalDelayed,
            'isDepartureDelayed' => (bool) $this->isDepartureDelayed,
            'cancelled' => (bool) ($this->cancelled ?? false),
        ];
    }
}
