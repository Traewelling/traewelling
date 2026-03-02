<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'LeaderboardUserResource',
    required: ['user', 'totalDuration', 'totalDistance', 'points'],
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/LightUserResource'),
        new OA\Property(property: 'totalDuration', description: 'duration travelled in minutes', type: 'integer', example: 6),
        new OA\Property(property: 'totalDistance', description: 'distance travelled in meters', type: 'integer', example: 12345),
        new OA\Property(property: 'points', description: 'points of user', type: 'integer'),
    ],
    type: 'object'
)]
class LeaderboardUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'user' => new LightUserResource($this->user),
            'totalDuration' => (int) $this->duration,
            'totalDistance' => (float) $this->distance,
            'points' => (int) $this->points,
        ];
    }
}
