<?php

namespace App\Http\Resources;

use App\Models\Station;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Station",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Karlsruhe Hbf"),
 *     @OA\Property(property="latitude", type="number", example="48.993207"),
 *     @OA\Property(property="longitude", type="number", example="8.400977"),
 *     @OA\Property(property="ibnr", type="string", example="8000191"),
 *     @OA\Property(property="rilIdentifier", type="string", example="RK")
 * )
 */
class StationResource extends JsonResource
{
    public function toArray($request): array {
        /** @var Station $this */
        return [
            "id"            => $this->id,
            "name"          => $this->name,
            "latitude"      => $this->latitude,
            "longitude"     => $this->longitude,
            "ibnr"          => $this->ibnr,
            "rilIdentifier" => $this->rilIdentifier
        ];
    }
}
