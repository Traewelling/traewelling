<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\RouteSegment;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'RouteSegmentResource',
    required: ['id', 'fromStationId', 'toStationId', 'distance', 'pathType', 'polyline', 'polylinePrecision'],
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '01960000-0000-7000-8000-000000000001'),
        new OA\Property(property: 'fromStationId', type: 'integer', example: 8000105),
        new OA\Property(property: 'toStationId', type: 'integer', example: 8000261),
        new OA\Property(property: 'distance', description: 'Distance in meters', type: 'integer', example: 42300, nullable: true),
        new OA\Property(property: 'pathType', type: 'string', example: 'rails', nullable: true),
        new OA\Property(property: 'polyline', description: 'Google Encoded Polyline', type: 'string', example: '_p~iF~ps|U_ulLnnqC_mqNvxq`@'),
        new OA\Property(property: 'polylinePrecision', type: 'integer', example: 5),
    ],
)]
class RouteSegmentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var RouteSegment $this */
        return [
            'id' => $this->id,
            'fromStationId' => $this->from_station_id,
            'toStationId' => $this->to_station_id,
            'distance' => $this->distance,
            'pathType' => $this->path_type,
            'polyline' => $this->polyline,
            'polylinePrecision' => $this->polyline_precision,
        ];
    }
}
