<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookResponseCodeStatsDto',
    required: ['responseCode', 'total'],
    properties: [
        new OA\Property(property: 'responseCode', type: 'integer', example: 200, nullable: true),
        new OA\Property(property: 'total', type: 'integer', example: 120),
    ],
    type: 'object'
)]
readonly class WebhookResponseCodeStatsDto
{
    public function __construct(
        public ?int $responseCode,
        public int $total,
    ) {}
}
