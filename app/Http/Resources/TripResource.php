<?php

namespace App\Http\Resources;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     title="TripResource",
 *     @OA\Property(property="id", type="int", example=1),
 *     @OA\Property(property="category", ref="#/components/schemas/HafasTravelType"),
 *     @OA\Property(property="mode", ref="#/components/schemas/MotisCategory", nullable=true),
 *     @OA\Property(property="number", type="string", example="4-a6s4-4"),
 *     @OA\Property(property="lineName", type="string", example="S 4"),
 *     @OA\Property(property="journeyNumber", type="int", example="34427"),
 *     @OA\Property(property="origin", ref="#/components/schemas/Station"),
 *     @OA\Property(property="destination", ref="#/components/schemas/Station"),
 *     @OA\Property(property="stopovers", type="array",
 *         @OA\Items(
 *             ref="#/components/schemas/StopoverResource"
 *         )
 *     ),
 *     @OA\Property(property="dataSource", ref="#/components/schemas/DataSourceResource", nullable=true),
 *  )
 */
class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array {
        /** @var Trip $this */
        return [
            'id'                => $this->id,
            'category'          => $this->category->value,
            'mode'              => $this->mode ? $this->mode->value : null,
            'number'            => $this->number,
            'lineName'          => $this->linename,
            'routeColor'        => $this->route_color,
            'routeTextColor'    => $this->route_text_color,
            'journeyNumber'     => $this->journey_number,
            'origin'            => new StationResource($this->originStation),
            'destination'       => new StationResource($this->destinationStation),
            'stopovers'         => StopoverResource::collection($this->stopovers),
            'dataSource'        => $this->motisSourceLicense ? new DataSourceResource($this->motisSourceLicense) : null
        ];
    }
}
