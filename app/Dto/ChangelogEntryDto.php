<?php

namespace App\Dto;

use Carbon\CarbonInterface;

readonly class ChangelogEntryDto
{
    public function __construct(
        public string $tag,
        public string $title,
        public string $description,
        public CarbonInterface $created
    ) {}
}
