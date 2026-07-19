<?php

namespace App\Http\Resources;

use App\Models\Stopover;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StopoverResource',
    required: [
        'id',
        'stopoverId',
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
        new OA\Property(property: 'id', description: 'Station ID of this stopover. Not unique within a trip; use stopoverId to reference a specific stop.', type: 'integer', example: 12345),
        new OA\Property(property: 'stopoverId', description: 'Unique ID of this specific stopover within the trip.', type: 'integer', example: 987654),
        new OA\Property(
            property: 'name',
            description: 'name of the station',
            type: 'string',
            example: 'Karlsruhe Hbf',
        ),
        new OA\Property(
            property: 'rilIdentifier',
            description: 'Deprecated. Always null. Use the station identifiers endpoint instead.',
            type: 'string',
            example: null,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'evaIdentifier',
            description: 'Deprecated. Always null. Use the station identifiers endpoint instead.',
            type: 'string',
            example: null,
            nullable: true,
            deprecated: true,
        ),
        new OA\Property(
            property: 'arrival',
            description: 'Deprecated. Use arrivalReal (if not null) or arrivalPlanned instead.',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
            deprecated: true,
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
            description: 'Deprecated. Use departureReal (if not null) or departurePlanned instead.',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
            deprecated: true,
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
            'stopoverId' => (int) $this->id,
            'name' => $this->station->name,
            'rilIdentifier' => null, // @deprecated - remove after 2026-09-30
            'evaIdentifier' => null, // @deprecated - remove after 2026-09-30
            'identifiers' => $this->when(
                $this->relationLoaded('station') && $this->station->relationLoaded('stationIdentifiers'),
                fn () => StationIdentifierResource::collection($this->station->stationIdentifiers),
            ),
            'arrival' => $this->arrival?->toIso8601String(), // @deprecated - remove after 2026-09-30
            'arrivalPlanned' => $this->arrival_planned?->toIso8601String(),
            'arrivalReal' => $this->arrival_real?->toIso8601String(),
            'arrivalPlatformPlanned' => $this->arrival_platform_planned ?? null,
            'arrivalPlatformReal' => $this->arrival_platform_real ?? null,
            'departure' => $this->departure?->toIso8601String(), // @deprecated - remove after 2026-09-30
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
