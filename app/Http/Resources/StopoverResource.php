<?php

namespace App\Http\Resources;

use App\Models\Stopover;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StopoverResource',
    required: [
        'id',
        'uuid',
        'stopoverId',
        'station',
        'name',
        'identifiers',
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
        new OA\Property(
            property: 'id',
            description: 'Deprecated as station ID. Currently holds the station ID, which is not unique within a trip. Use station for station details. After 2026-11-30 this field will be repurposed to hold the unique stopover ID.',
            type: 'integer',
            example: 12345,
            deprecated: true,
        ),
        new OA\Property(
            property: 'uuid',
            description: 'Stable identifier of this stopover. Will become the primary key later.',
            type: 'string',
            format: 'uuid',
            example: '00000000-0000-0000-0000-000000000000',
            nullable: true,
        ),
        new OA\Property(
            property: 'stopoverId',
            description: 'Deprecated. Temporary field holding the unique ID of this specific stopover within the trip. Only available until id is repurposed to the stopover ID (after 2026-11-30), then removed.',
            type: 'integer',
            example: 987654,
            deprecated: true,
        ),
        new OA\Property(property: 'station', ref: StationResource::class),
        new OA\Property(
            property: 'name',
            description: 'Deprecated. Name of the station. Use station.name instead.',
            type: 'string',
            example: 'Karlsruhe Hbf',
            deprecated: true,
        ),
        new OA\Property(
            property: 'identifiers',
            description: 'Deprecated. Only present with withIdentifiers=true. Use station.identifiers instead.',
            type: 'array',
            items: new OA\Items(ref: StationIdentifierResource::class),
            deprecated: true,
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
            description: 'Deprecated. Please check if a stop is delayed by compare planned and real time.',
            type: 'boolean',
            example: false,
            deprecated: true,
        ),
        new OA\Property(
            property: 'isDepartureDelayed',
            description: 'Deprecated. Please check if a stop is delayed by compare planned and real time.',
            type: 'boolean',
            example: false,
            deprecated: true,
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
            'id' => (int) $this->train_station_id, // @deprecated - after 2026-11-30 this becomes $this->id (stopover ID); use station for station details
            'uuid' => $this->uuid,
            'stopoverId' => (int) $this->id, // @deprecated - temporary bridge until id is repurposed to the stopover ID, then removed
            'station' => new StationResource($this->whenLoaded('station')),
            'name' => $this->station->name, // @deprecated - use station.name instead
            'rilIdentifier' => null, // @deprecated - remove after 2026-09-30
            'evaIdentifier' => null, // @deprecated - remove after 2026-09-30
            'identifiers' => $this->when( // @deprecated - remove after 2026-11-30 -> use station.identifiers instead
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
            'isArrivalDelayed' => (bool) $this->isArrivalDelayed, // @deprecated - remove after 2026-11-30
            'isDepartureDelayed' => (bool) $this->isDepartureDelayed, // @deprecated - remove after 2026-11-30
            'cancelled' => (bool) ($this->cancelled ?? false),
        ];
    }
}
