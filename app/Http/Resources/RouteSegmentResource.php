<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RouteSegment;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'RouteSegmentResource',
    required: ['id', 'fromStation', 'toStation', 'distance', 'duration', 'pathType', 'polyline', 'polylinePrecision'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '01960000-0000-7000-8000-000000000001'),
        new OA\Property(property: 'fromStation', ref: '#/components/schemas/Station', nullable: true),
        new OA\Property(property: 'toStation', ref: '#/components/schemas/Station', nullable: true),
        new OA\Property(property: 'distance', description: 'Distance in meters', type: 'integer', example: 42300, nullable: true),
        new OA\Property(property: 'duration', description: 'Duration in seconds', type: 'integer', example: 5400, nullable: true),
        new OA\Property(property: 'pathType', type: 'string', example: 'rails', nullable: true),
        new OA\Property(property: 'polyline', description: 'Google Encoded Polyline', type: 'string', example: '_p~iF~ps|U_ulLnnqC_mqNvxq`@'),
        new OA\Property(property: 'polylinePrecision', type: 'integer', example: 5),
        new OA\Property(property: 'customWaypointsCount', description: 'Number of custom waypoints, or null if none set', type: 'integer', example: 4, nullable: true),
    ],
)]
class RouteSegmentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var RouteSegment $this */
        return [
            'id' => $this->id,
            'fromStation' => $this->whenLoaded('fromStation', fn () => new StationResource($this->fromStation)),
            'toStation' => $this->whenLoaded('toStation', fn () => new StationResource($this->toStation)),
            'distance' => $this->distance,
            'duration' => $this->duration,
            'pathType' => $this->path_type,
            'polyline' => $this->polyline,
            'polylinePrecision' => $this->polyline_precision,
            'customWaypointsCount' => $this->when(
                $this->custom_waypoints !== null,
                fn () => count($this->custom_waypoints),
            ),
        ];
    }
}
