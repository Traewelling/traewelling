<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CursorPaginationMeta',
    title: 'CursorPaginationMeta',
    description: 'Cursor pagination meta data',
    required: ['path', 'per_page', 'next_cursor', 'prev_cursor'],
    properties: [
        new OA\Property(property: 'path', type: 'string', format: 'url', example: 'https://traewelling.de/api/v2/ENDPOINT'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'next_cursor', type: 'string', example: 'eyJjcmVhdGVkX2F0IjoiMjAyNi0xMS0wNyAxMjowMDowMCJ9', nullable: true),
        new OA\Property(property: 'prev_cursor', type: 'string', example: null, nullable: true),
    ],
)]
class CursorPaginationMetaResource {}
