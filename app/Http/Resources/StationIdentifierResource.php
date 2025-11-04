<?php

namespace App\Http\Resources;

use App\Models\StationIdentifier;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="StationIdentifier",
 *     @OA\Property(property="type", type="string", example="de_db_ril100"),
 *     @OA\Property(property="identifier", type="string", example="RK"),
 * )
 */
class StationIdentifierResource extends JsonResource
{

    public function toArray($request): array {
        /** @var StationIdentifier $this */
        return [
            'type'       => $this->type,
            'identifier' => $this->identifier,
        ];
    }
}
