<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'LeaderboardUserResource',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/LightUserResource'),
        new OA\Property(property: 'totalDuration', type: 'integer', description: 'duration travelled in minutes', example: 6),
        new OA\Property(property: 'totalDistance', type: 'integer', description: 'distance travelled in meters', example: 12345),
        new OA\Property(property: 'points', type: 'integer', description: 'points of user'),
    ],
    type: 'object',
    required: ['user', 'totalDuration', 'totalDistance', 'points']
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
