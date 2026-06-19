<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Dto\Internal\CheckinSuccessDto;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PointsCalculation',
    title: 'PointsCalculation',
    required: ['base', 'distance', 'factor', 'reason'],
    properties: [
        new OA\Property(property: 'base', description: 'Basepoints for this type of vehicle', type: 'number', format: 'float', example: 0.5),
        new OA\Property(property: 'distance', description: 'Points for the travelled distance', type: 'number', format: 'float', example: 0.25),
        new OA\Property(property: 'factor', type: 'number', format: 'float', example: 0.25),
        new OA\Property(property: 'reason', ref: '#/components/schemas/PointReason', example: 1),
    ],
)]
#[OA\Schema(
    schema: 'Points',
    title: 'Points',
    description: 'Points model',
    required: ['points', 'calculation', 'additional'],
    properties: [
        new OA\Property(property: 'points', description: 'points', type: 'integer', example: 1),
        new OA\Property(property: 'calculation', ref: '#/components/schemas/PointsCalculation'),
        new OA\Property(property: 'additional', description: 'Deprecated. Always null.', type: 'array', items: new OA\Items(), nullable: true, deprecated: true),
    ],
)]
#[OA\Schema(
    title: 'CheckinResponse',
    required: ['status', 'points', 'alsoOnThisConnection'],
    properties: [
        new OA\Property(
            property: 'status',
            ref: '#/components/schemas/StatusResource',
            description: 'StatusModel of the created status',
        ),
        new OA\Property(
            property: 'points',
            ref: '#/components/schemas/Points',
            description: 'points and reasons for the points',
        ),
        new OA\Property(
            property: 'alsoOnThisConnection',
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
            'points' => [
                'points' => $this->pointCalculation->points,
                'calculation' => [
                    'base' => $this->pointCalculation->base,
                    'distance' => $this->pointCalculation->distance,
                    'factor' => $this->pointCalculation->factor,
                    'reason' => $this->pointCalculation->reason->value,
                ],
                'additional' => null, // @deprecated - remove after 2026-09-30
            ],
            'alsoOnThisConnection' => StatusResource::collection($this->alsoOnThisConnection),
        ];
    }
}
