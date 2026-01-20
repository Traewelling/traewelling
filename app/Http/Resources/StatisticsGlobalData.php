<?php

namespace App\Http\Resources;

use App\Dto\Internal\GlobalCheckinStats;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="StatisticsGlobalData",
 *     required={"distance", "duration", "activeUsers"},
 *
 *     @OA\Property(
 *                      property="distance",
 *                      description="Globally travelled distance in meters",
 *                      type="integer",
 *                      example=1000
 *                  ),
 *                  @OA\Property(
 *                      property="duration",
 *                      description="Globally travelled duration in minutes",
 *                      type="integer",
 *                      example=1000
 *                  ),
 *                  @OA\Property(
 *                      property="activeUsers",
 *                      description="Number of active users",
 *                      type="integer",
 *                      example=1000
 *                 ),
 * )
 */
class StatisticsGlobalData extends JsonResource
{
    public function toArray($request): array
    {
        /** @var GlobalCheckinStats $this */
        return [
            'distance' => $this->distance,
            'duration' => $this->duration,
            'activeUsers' => $this->userCount,
        ];
    }
}
