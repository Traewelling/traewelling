<?php

declare(strict_types=1);

namespace App\Virtual\Models\Laravel;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'Meta',
    description: 'Pagination meta data',
    xml: new OA\Xml(name: 'PaginationMeta'),
)]
class PaginationMeta
{
    #[OA\Property(
        title: 'current_page',
        description: 'currently displayed page in this pagination',
        type: 'integer',
        example: 2,
    )]
    private int $current_page;

    #[OA\Property(
        title: 'from',
        description: 'The first element on this page is the nth element of the query',
        type: 'integer',
        example: 16,
    )]
    private int $from;

    #[OA\Property(
        title: 'path',
        description: 'The path of this pagination',
        type: 'string',
        format: 'url',
        example: 'https://traewelling.de/api/v1/ENDPOINT',
    )]
    private string $path;

    #[OA\Property(
        title: 'per_page',
        description: 'the amount of items per page in this pagination',
        type: 'integer',
        example: 15,
    )]
    private int $per_page;

    #[OA\Property(
        title: 'to',
        description: 'The last element on this page is the nth element of the query',
        type: 'integer',
        example: 30,
    )]
    private int $to;
}
