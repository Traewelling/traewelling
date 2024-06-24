<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="StatusTagResource",
 *     type="object",
 *     title="StatusTagResource",
 *     @OA\Property(property="key", type="string", example="trwl:vehicle_number"),
 *     @OA\Property(property="value", type="string", example="94 80 0450 921 D-AVG"),
 *     @OA\Property(property="visibility", type="integer", example="1"),
 *     )
 */
class StatusTagResource extends JsonResource
{

    public function toArray($request): array {
        return [
            'key'        => $this->key,
            'value'      => $this->value,
            'visibility' => $this->visibility->value,
        ];
    }
}
