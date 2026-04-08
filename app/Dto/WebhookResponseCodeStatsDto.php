<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookResponseCodeStatsDto',
    required: ['response_code', 'total'],
    properties: [
        new OA\Property(property: 'response_code', type: 'integer', nullable: true, example: 200),
        new OA\Property(property: 'total', type: 'integer', example: 120),
    ],
    type: 'object'
)]
readonly class WebhookResponseCodeStatsDto
{
    public function __construct(
        public ?int $response_code,
        public int $total,
    ) {}
}
