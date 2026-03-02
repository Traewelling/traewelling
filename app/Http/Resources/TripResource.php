<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'TripResource',
    properties: [
        new OA\Property(property: 'id', type: 'int', example: 1),
        new OA\Property(property: 'category', ref: '#/components/schemas/HafasTravelType'),
        new OA\Property(property: 'mode', ref: '#/components/schemas/MotisCategory', nullable: true),
        new OA\Property(property: 'number', type: 'string', example: '4-a6s4-4'),
        new OA\Property(property: 'lineName', type: 'string', example: 'S 4'),
        new OA\Property(property: 'journeyNumber', type: 'int', example: '34427'),
        new OA\Property(property: 'origin', ref: '#/components/schemas/Station'),
        new OA\Property(property: 'destination', ref: '#/components/schemas/Station'),
        new OA\Property(
            property: 'stopovers',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/StopoverResource'),
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
            'category' => $this->category->value,
            'mode' => $this->mode ? $this->mode->value : null,
            'number' => $this->number,
            'lineName' => $this->linename,
            'routeColor' => $this->route_color,
            'routeTextColor' => $this->route_text_color,
            'journeyNumber' => $this->journey_number,
            'origin' => new StationResource($this->originStation),
            'destination' => new StationResource($this->destinationStation),
            'stopovers' => StopoverResource::collection($this->stopovers),
            'dataSource' => $this->motisSourceLicense ? new DataSourceResource($this->motisSourceLicense) : null,
        ];
    }
}
