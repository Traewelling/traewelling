<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'LikeResponse',
    xml: new OA\Xml(name: 'LikeResponse'),
)]
class LikeResponse
{
    #[OA\Property(
        title: 'count',
        description: 'Amount of likes',
        type: 'integer',
        format: 'int32',
        example: 12,
    )]
    private string $count;
}
