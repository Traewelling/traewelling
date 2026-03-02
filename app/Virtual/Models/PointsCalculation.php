<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'PointsCalculation',
    xml: new OA\Xml(name: 'PointsCalculation'),
)]
class PointsCalculation
{
    #[OA\Property(
        title: 'base',
        description: 'Basepoints for this type of vehicle',
        type: 'number',
        format: 'float',
        example: 0.5,
    )]
    private float $base;

    #[OA\Property(
        title: 'distance',
        description: 'Points for the travelled distance',
        type: 'number',
        format: 'float',
        example: 0.25,
    )]
    public float $distance;

    #[OA\Property(
        title: 'factor',
        type: 'number',
        format: 'float',
        example: 0.25,
    )]
    public float $factor;

    #[OA\Property(
        title: 'reason',
        ref: '#/components/schemas/PointReason',
        example: 1,
    )]
    public mixed $reason;
}
