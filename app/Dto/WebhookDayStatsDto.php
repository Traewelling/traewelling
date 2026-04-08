<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookDayStatsDto',
    required: ['date', 'total', 'success', 'failed'],
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-04-01'),
        new OA\Property(property: 'total', type: 'integer', example: 20),
        new OA\Property(property: 'success', type: 'integer', example: 15),
        new OA\Property(property: 'failed', type: 'integer', example: 5),
    ],
    type: 'object'
)]
readonly class WebhookDayStatsDto
{
    public function __construct(
        public string $date,
        public int $total,
        public int $success,
        public int $failed,
    ) {}
}
