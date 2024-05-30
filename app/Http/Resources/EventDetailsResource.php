<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="EventDetails",
 *     @OA\Property(property="id", type="integer", example=39),
 *     @OA\Property(property="slug", type="string", example="9_euro_ticket"),
 *     @OA\Property(property="trainDistance", type="integer", example=12345),
 *     @OA\Property(property="trainDuration", type="integer", example=12345)
 * )
 */
class EventDetailsResource extends JsonResource
{

    public function toArray($request): array {
        return [
            "id"            => $this->id,
            "slug"          => $this->slug,
            "trainDistance" => $this->totalDistance, // @todo: rename key - we have more than just trains
            "trainDuration" => $this->totalDuration, // @todo: rename key - we have more than just trains
        ];
    }
}
