<?php

declare(strict_types=1);

namespace App\Virtual\Models\Parameters;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'PaginationPage',
    description: 'pagination links',
    xml: new OA\Xml(name: 'PaginationPage'),
)]
class PaginationPage
{
    #[OA\Parameter(
        name: 'page',
        description: 'Page of pagination',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer'),
    )]
    public int $page;
}
