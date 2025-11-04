<?php

namespace App\Http\Resources;

use App\Http\Controllers\Backend\Transport\dtos\StationDto;
use App\Models\Station;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="Station",
 *     required={"id", "name", "latitude", "longitude", "ibnr", "rilIdentifier", "areas"},
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
    private bool $areasSet;

    public function __construct($station) {
        $this->areasSet = $station instanceof Station || $station instanceof StationDto;

        parent::__construct($station);
    }

    public function toArray($request): array {
        /** @var Station $this */
        return [
            "id"            => $this->id,
            "name"          => $this->name,
            "latitude"      => $this->latitude,
            "longitude"     => $this->longitude,
            "ibnr"          => $this->ibnr, // @deprecated - see identifiers
            "rilIdentifier" => $this->rilIdentifier, // @deprecated - see identifiers
            "areas"         => $this->areasSet ? AreaResource::collection($this->whenLoaded('areas')) : null,
            'identifiers'   => StationIdentifierResource::collection($this->whenLoaded('stationIdentifiers')),
        ];
    }
}
