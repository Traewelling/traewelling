<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * @phpstan-type PurposeBreakdown array{reason: string|null, count: int, distance: int}
 * @phpstan-type CategoryBreakdown array{name: string|null, count: int, distance: int}
 * @phpstan-type OperatorBreakdown array{name: string|null, count: int, distance: int}
 */
readonly class TicketStatisticsDto
{
    /**
     * @param  array<PurposeBreakdown>  $purposes
     * @param  array<CategoryBreakdown>  $categories
     * @param  array<OperatorBreakdown>  $operators
     */
    public function __construct(
        public int $tripCount,
        public int $distance,
        public int $duration,
        public ?string $firstUsed,
        public ?string $lastUsed,
        public ?float $costPerTrip,
        public ?float $costPerKm,
        public ?float $costPerHour,
        public array $purposes,
        public array $categories,
        public array $operators,
    ) {}
}
