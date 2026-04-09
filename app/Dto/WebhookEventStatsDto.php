<?php

declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: 'WebhookEventStatsDto',
    required: ['event', 'total'],
    properties: [
        new OA\Property(property: 'event', type: 'string', example: 'checkin_create'),
        new OA\Property(property: 'total', type: 'integer', example: 100),
    ],
    type: 'object'
)]
readonly class WebhookEventStatsDto
{
    public function __construct(
        public string $event,
        public int $total,
    ) {}
}
