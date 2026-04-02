<?php

namespace App\Dto;

readonly class ChangelogItemDto
{
    public function __construct(
        public string $markdownLine,
        public string $type,
        public string $shortenedLine,
    ) {}
}
