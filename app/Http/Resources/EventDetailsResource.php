<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'EventDetails',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 39),
        new OA\Property(property: 'slug', type: 'string', example: '9_euro_ticket'),
        new OA\Property(property: 'trainDistance', type: 'integer', example: 12345),
        new OA\Property(property: 'trainDuration', type: 'integer', example: 12345),
    ],
)]
class EventDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'trainDistance' => $this->totalDistance, // @todo: rename key - we have more than just trains
            'trainDuration' => $this->totalDuration, // @todo: rename key - we have more than just trains
        ];
    }
}
