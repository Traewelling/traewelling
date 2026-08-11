<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TripResource',
    required: ['id', 'uuid', 'tripId', 'category', 'mode', 'number', 'lineName', 'journeyNumber', 'origin', 'destination', 'operator', 'stopovers', 'checkinCount', 'dataSource'],
    properties: [
        new OA\Property(property: 'id', type: 'int', example: 1),
        new OA\Property(property: 'uuid', description: 'Stable identifier of this trip. Will become the primary key later.', type: 'string', format: 'uuid', example: '00000000-0000-0000-0000-000000000000', nullable: true),
        new OA\Property(property: 'tripId', description: 'Internal trip identifier (use this for the checkin flow)', type: 'string', example: '00000000-0000-0000-0000-000000000000'),
        new OA\Property(property: 'category', ref: '#/components/schemas/HafasTravelType'),
        new OA\Property(property: 'mode', ref: '#/components/schemas/MotisCategory', nullable: true),
        new OA\Property(property: 'number', type: 'string', example: '4-a6s4-4'),
        new OA\Property(property: 'lineName', type: 'string', example: 'S 4'),
        new OA\Property(property: 'journeyNumber', type: 'int', example: '34427'),
        new OA\Property(property: 'origin', ref: '#/components/schemas/Station'),
        new OA\Property(property: 'destination', ref: '#/components/schemas/Station'),
        new OA\Property(property: 'operator', ref: OperatorResource::class, nullable: true),
        new OA\Property(
            property: 'stopovers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/StopoverResource'),
        ),
        new OA\Property(
            property: 'checkinCount',
            description: 'Total number of checkins on this trip, including those you cannot see. A trip can only be deleted while this is 0.',
            type: 'integer',
            example: 3,
        ),
        new OA\Property(
            property: 'dataSource',
            ref: '#/components/schemas/DataSourceResource',
            nullable: true,
        ),
    ],
)]
class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        /** @var Trip $this */
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'tripId' => $this->trip_id,
            'category' => $this->category->value,
            'mode' => $this->mode ? $this->mode->value : null,
            'number' => $this->number,
            'lineName' => $this->linename,
            'routeColor' => $this->route_color,
            'routeTextColor' => $this->route_text_color,
            'journeyNumber' => $this->journey_number,
            'origin' => new StationResource($this->originStation),
            'destination' => new StationResource($this->destinationStation),
            'operator' => $this->operator ? new OperatorResource($this->operator) : null,
            'stopovers' => StopoverResource::collection($this->stopovers),
            'checkinCount' => (int) ($this->checkins_count ?? $this->checkins()->count()),
            'dataSource' => $this->motisSourceLicense ? new DataSourceResource($this->motisSourceLicense) : null,
        ];
    }
}
