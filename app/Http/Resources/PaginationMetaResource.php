<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

/** Shared OA schema for Laravel pagination meta data. Not a real resource. */
#[OA\Schema(
    schema: 'PaginationMeta',
    title: 'Meta',
    description: 'Pagination meta data',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 2),
        new OA\Property(property: 'from', type: 'integer', example: 16),
        new OA\Property(property: 'path', type: 'string', format: 'url', example: 'https://traewelling.de/api/v1/ENDPOINT'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'to', type: 'integer', example: 30),
    ],
)]
class PaginationMetaResource {}
