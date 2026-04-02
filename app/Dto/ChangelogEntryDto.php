<?php

namespace App\Dto;

use Carbon\CarbonInterface;

readonly class ChangelogEntryDto
{
    public function __construct(
        public string $tag,
        public string $title,
        public string $description,
        /** @property ChangelogItemDto[] $entries */
        public array $entries,
        public CarbonInterface $created
    ) {}
}
