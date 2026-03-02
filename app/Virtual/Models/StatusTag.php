<?php

declare(strict_types=1);

namespace App\Virtual\Models;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'StatusTag',
    description: 'StatusTag model',
    xml: new OA\Xml(name: 'StatusTag'),
)]
class StatusTag
{
    #[OA\Property(
        title: 'key',
        description: 'Key of tag',
        type: 'string',
        example: 'trwl:ticket',
    )]
    private string $key;

    #[OA\Property(
        title: 'value',
        description: 'Value of tag',
        type: 'string',
        example: 'BahnCard 100',
    )]
    private string $value;

    #[OA\Property(ref: '#/components/schemas/StatusVisibility')]
    private string $visibility;
}
