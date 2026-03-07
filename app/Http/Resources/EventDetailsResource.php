<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'EventDetails',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 39),
        new OA\Property(property: 'slug', type: 'string', example: '9_euro_ticket'),
        new OA\Property(property: 'totalDistance', description: 'distance travelled in meters', type: 'integer', example: 12345),
        new OA\Property(property: 'totalDuration', description: 'duration travelled in minutes', type: 'integer', example: 12345),
        new OA\Property(property: 'trainDistance', description: 'Deprecated. Use totalDistance instead.', type: 'integer', example: 12345, deprecated: true),
        new OA\Property(property: 'trainDuration', description: 'Deprecated. Use totalDuration instead.', type: 'integer', example: 12345, deprecated: true),
    ],
)]
class EventDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'totalDistance' => $this->totalDistance,
            'totalDuration' => $this->totalDuration,
            'trainDistance' => $this->totalDistance, // @deprecated - remove after 2026-09-30
            'trainDuration' => $this->totalDuration, // @deprecated - remove after 2026-09-30
        ];
    }
}
