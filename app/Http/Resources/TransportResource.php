<?php

namespace App\Http\Resources;

use App\Enum\StatusTagKey;
use App\Models\Checkin;
use App\Models\StatusTag;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TransportResource',
    required: [
        'trip',
        'hafasId',
        'category',
        'number',
        'lineName',
        'routeColor',
        'routeTextColor',
        'journeyNumber',
        'manualJourneyNumber',
        'distance',
        'points',
        'duration',
        'manualDeparture',
        'manualArrival',
        'origin',
        'destination',
        'mode',
        'operator',
        'dataSource',
    ],
    properties: [
        new OA\Property(property: 'trip', type: 'integer', example: '4711'),
        new OA\Property(property: 'hafasId', type: 'string', example: '1|1234|567'),
        new OA\Property(property: 'category', ref: '#/components/schemas/HafasTravelType'),
        new OA\Property(property: 'mode', ref: '#/components/schemas/MotisCategory', nullable: true),
        new OA\Property(
            property: 'number',
            description: 'Internal number of the journey',
            example: '4-a6s8-8',
        ),
        new OA\Property(property: 'lineName', type: 'string', example: 'S 1'),
        new OA\Property(
            property: 'routeColor',
            description: 'Hex color code of the route, if available',
            type: 'string',
            example: 'FFEE00',
            nullable: true,
        ),
        new OA\Property(
            property: 'routeTextColor',
            description: 'Hex color code of the route text, if available',
            type: 'string',
            example: 'FFFFFF',
            nullable: true,
        ),
        new OA\Property(property: 'journeyNumber', type: 'integer', example: 85639),
        new OA\Property(
            property: 'manualJourneyNumber',
            description: 'Manual journey number, if set by the user. This is intended for use cases like ICE lines in germany that have line number but are more widely known by their train number',
            type: 'string',
            example: 'ICE 4',
            nullable: true,
        ),
        new OA\Property(
            property: 'distance',
            description: 'Distance in meters',
            type: 'integer',
            example: 10000,
        ),
        new OA\Property(property: 'points', type: 'integer', example: 37),
        new OA\Property(
            property: 'duration',
            description: 'Duration in minutes',
            type: 'integer',
            example: 30,
        ),
        new OA\Property(
            property: 'manualDeparture',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(
            property: 'manualArrival',
            type: 'string',
            format: 'date-time',
            example: '2022-07-17T13:37:00+02:00',
            nullable: true,
        ),
        new OA\Property(property: 'origin', ref: '#/components/schemas/StopoverResource'),
        new OA\Property(property: 'destination', ref: '#/components/schemas/StopoverResource'),
        new OA\Property(
            property: 'operator',
            ref: '#/components/schemas/OperatorResource',
            nullable: true,
        ),
        new OA\Property(
            property: 'dataSource',
            ref: '#/components/schemas/DataSourceResource',
            nullable: true,
        ),
    ],
)]
class TransportResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Checkin $this */
        $pointsEnabled = $request->user()?->points_enabled ?? true;
        $manualJourneyNumber = $this->relationLoaded('statusTags')
            ? $this->statusTags->firstWhere('key', StatusTagKey::JOURNEY_NUMBER->value)
            : StatusTag::whereStatusId($this->status_id)->whereRaw('`key` = ?', [StatusTagKey::JOURNEY_NUMBER->value])->first();

        if ($manualJourneyNumber !== null && Gate::denies('view', $manualJourneyNumber)) {
            $manualJourneyNumber = null;
        }

        return [
            'trip' => (int) $this->trip->id,
            'hafasId' => (string) $this->trip->trip_id,
            'category' => (string) $this->trip->category->value,
            'mode' => $this->trip->mode ? (string) $this->trip->mode->value : null,
            'number' => (string) $this->trip->number,
            'lineName' => (string) $this->trip->linename,
            'routeColor' => $this->trip->route_color,
            'routeTextColor' => $this->trip->route_text_color,
            'journeyNumber' => $this->trip->journey_number,
            'manualJourneyNumber' => $manualJourneyNumber ? $manualJourneyNumber->value : null,
            'distance' => (int) $this->distance,
            'points' => (int) $pointsEnabled ? $this->points : 0,
            'duration' => (int) $this->duration,
            'manualDeparture' => $this->manual_departure?->toIso8601String(),
            'manualArrival' => $this->manual_arrival?->toIso8601String(),
            'origin' => new StopoverResource($this->originStopover),
            'destination' => new StopoverResource($this->destinationStopover),
            'operator' => $this?->trip->operator ? new OperatorResource($this?->trip->operator) : null,
            'dataSource' => $this->trip->motisSourceLicense ? new DataSourceResource($this->trip->motisSourceLicense) : null,
        ];
    }
}
