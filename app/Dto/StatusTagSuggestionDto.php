<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\TagSuggestionSource;

readonly class StatusTagSuggestionDto
{
    public function __construct(
        public string $key,
        public string $value,
        public TagSuggestionSource $source,
    ) {}

    public function fingerprint(): string
    {
        return $this->key . ':' . $this->value;
    }
}
