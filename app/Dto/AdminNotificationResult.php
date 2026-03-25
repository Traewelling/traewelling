<?php

declare(strict_types=1);

namespace App\Dto;

readonly class AdminNotificationResult
{
    public function __construct(
        public ?int $telegramId,
        public ?string $matrixId,
    ) {}

    public function hasAny(): bool
    {
        return $this->telegramId !== null || $this->matrixId !== null;
    }
}
