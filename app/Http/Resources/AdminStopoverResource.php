<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Stopover;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'AdminStopover',
    required: ['id', 'uuid', 'station', 'arrivalPlanned', 'arrivalReal', 'departurePlanned', 'departureReal', 'routeSegmentId', 'routeSegmentType', 'stationIdentifierId'],
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'station',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'arrivalPlanned', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'arrivalReal', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'departurePlanned', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'departureReal', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'routeSegmentId', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'routeSegmentType', type: 'string', nullable: true, enum: ['identifier', 'station']),
        new OA\Property(property: 'stationIdentifierId', type: 'string', format: 'uuid', nullable: true),
    ],
)]
class AdminStopoverResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Stopover $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'station' => [
                'id' => $this->station->id,
                'name' => $this->station->name,
            ],
            'arrivalPlanned' => $this->arrival_planned?->toIso8601String(),
            'arrivalReal' => $this->arrival_real?->toIso8601String(),
            'departurePlanned' => $this->departure_planned?->toIso8601String(),
            'departureReal' => $this->departure_real?->toIso8601String(),
            'routeSegmentId' => $this->route_segment_id,
            'routeSegmentType' => $this->route_segment_id
                ? ($this->routeSegment?->from_identifier_id && $this->routeSegment?->to_identifier_id
                    ? 'identifier'
                    : 'station')
                : null,
            'stationIdentifierId' => $this->station_identifier_id,
        ];
    }
}
