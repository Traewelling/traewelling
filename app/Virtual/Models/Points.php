<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Points',
    description: 'Points model',
    xml: new OA\Xml(name: 'Points'),
)]
class Points
{
    #[OA\Property(
        title: 'points',
        description: 'points',
        type: 'integer',
        format: 'int',
        example: 1,
    )]
    private int $points;

    #[OA\Property(
        title: 'calculation',
        description: '',
        ref: '#/components/schemas/PointsCalculation',
    )]
    public mixed $calculation;

    #[OA\Property(
        title: 'additional',
        description: 'extra points that can be given',
        type: 'array',
        items: new OA\Items(example: ['identifier' => 'extrapoints', 'points' => 4, 'divisibile' => false]),
    )]
    private array $additional;
}
