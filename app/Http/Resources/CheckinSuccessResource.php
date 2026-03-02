<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\Internal\CheckinSuccessDto;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PointsCalculation',
    title: 'PointsCalculation',
    properties: [
        new OA\Property(property: 'base', type: 'number', format: 'float', description: 'Basepoints for this type of vehicle', example: 0.5),
        new OA\Property(property: 'distance', type: 'number', format: 'float', description: 'Points for the travelled distance', example: 0.25),
        new OA\Property(property: 'factor', type: 'number', format: 'float', example: 0.25),
        new OA\Property(property: 'reason', ref: '#/components/schemas/PointReason', example: 1),
    ],
)]
#[OA\Schema(
    schema: 'Points',
    title: 'Points',
    description: 'Points model',
    properties: [
        new OA\Property(property: 'points', type: 'integer', description: 'points', example: 1),
        new OA\Property(property: 'calculation', ref: '#/components/schemas/PointsCalculation'),
        new OA\Property(property: 'additional', type: 'array', nullable: true, description: 'extra points that can be given', items: new OA\Items()),
    ],
)]
#[OA\Schema(
    title: 'CheckinResponse',
    properties: [
        new OA\Property(
            property: 'status',
            description: 'StatusModel of the created status',
            ref: '#/components/schemas/StatusResource',
        ),
        new OA\Property(
            property: 'points',
            description: 'points and reasons for the points',
            ref: '#/components/schemas/Points',
        ),
        new OA\Property(
            property: 'alsoOnThisconnection',
            description: 'Statuses of other people on this connection',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/StatusResource'),
        ),
    ],
)]
class CheckinSuccessResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var CheckinSuccessDto $this */
        return [
            'status' => new StatusResource($this->status),
            // ToDo: Rewrite ['points'] so the DTO will match the documented structure -> non-breaking api change
            'points' => [
                'points' => $this->pointCalculation->points,
                'calculation' => [
                    'base' => $this->pointCalculation->basePoints,
                    'distance' => $this->pointCalculation->distancePoints,
                    'factor' => $this->pointCalculation->factor,
                    'reason' => $this->pointCalculation->reason->value,
                ],
                'additional' => null, // unused old attribute (not removed so this isn't breaking)
            ],
            'alsoOnThisConnection' => StatusResource::collection($this->alsoOnThisConnection),
        ];
    }
}
