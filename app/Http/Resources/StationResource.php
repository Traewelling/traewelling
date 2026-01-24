<?php

namespace App\Http\Resources;

use App\Models\Station;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Station",
 *     required={"id", "name", "latitude", "longitude", "areas"},
 *
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Karlsruhe Hbf"),
 *     @OA\Property(property="latitude", type="number", example="48.993207"),
 *     @OA\Property(property="longitude", type="number", example="8.400977"),
 *     @OA\Property(property="areas", type="array", @OA\Items(ref="#/components/schemas/AreaResource")),
 *     @OA\Property(property="identifiers", type="array", @OA\Items(ref="#/components/schemas/StationIdentifierResource")),
 * )
 */
class StationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Station $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ibnr' => $this->getIdentifier(\App\StationIdentifierType::DE_DB_IBNR), // @deprecated - see identifiers
            'rilIdentifier' => $this->getIdentifier(\App\StationIdentifierType::DE_DB_RIL100), // @deprecated - see identifiers
            'areas' => AreaResource::collection($this->whenLoaded('areas')),
            'identifiers' => StationIdentifierResource::collection($this->stationIdentifiers),
        ];
    }
}
