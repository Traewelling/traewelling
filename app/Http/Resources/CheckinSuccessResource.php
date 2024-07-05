<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\Internal\CheckinSuccessDto;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="CheckinResponse",
 *     @OA\Property(property="status", description="StatusModel of the created status", ref="#/components/schemas/StatusResource"),
 *     @OA\Property(property="points", description="points and reasons for the points", ref="#/components/schemas/Points"),
 *     @OA\Property(property="alsoOnThisconnection", description="Statuses of other people on this connection", type="array", @OA\Items(ref="#/components/schemas/StatusResource"))
 * )
 */
class CheckinSuccessResource extends JsonResource
{
    public function toArray($request): array {
        /** @var CheckinSuccessDto $this */
        return [
            'status' => new StatusResource($this->status),
            //ToDo: Rewrite ['points'] so the DTO will match the documented structure -> non-breaking api change
            'points' => [
                'points'      => $this->pointCalculation->points,
                'calculation' => [
                    'base'     => $this->pointCalculation->basePoints,
                    'distance' => $this->pointCalculation->distancePoints,
                    'factor'   => $this->pointCalculation->factor,
                    'reason'   => $this->pointCalculation->reason->value,
                ],
                'additional'  => null, //unused old attribute (not removed so this isn't breaking)
            ],
            'alsoOnThisConnection' => StatusResource::collection($this->alsoOnThisConnection)
        ];
    }
}
