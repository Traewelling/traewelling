<?php

declare(strict_types=1);

namespace App\Virtual\Models\Laravel;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Links',
    description: 'pagination links',
    xml: new OA\Xml(name: 'Links'),
)]
class Links
{
    #[OA\Property(
        title: 'first',
        description: 'URL to first page of this pagination',
        type: 'string',
        format: 'uri',
        nullable: true,
        example: 'https://traewelling.de/api/v1/ENDPOINT?page=1',
    )]
    private string $first;

    #[OA\Property(
        title: 'last',
        description: 'URL to last page of this pagination (mostly null)',
        type: 'string',
        format: 'uri',
        nullable: true,
        example: null,
    )]
    private string $last;

    #[OA\Property(
        title: 'prev',
        description: 'URL to previous page of this pagination (mostly null)',
        type: 'string',
        format: 'uri',
        nullable: true,
        example: 'https://traewelling.de/api/v1/ENDPOINT?page=1',
    )]
    private string $prev;

    #[OA\Property(
        title: 'next',
        description: 'URL to next page of this pagination (mostly null)',
        type: 'string',
        format: 'uri',
        nullable: true,
        example: 'https://traewelling.de/api/v1/ENDPOINT?page=2',
    )]
    private string $next;
}
