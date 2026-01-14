<?php

namespace App\Dto\Internal;

readonly class IcsExportStatusTag
{
    public function __construct(
        public string $key,
        public string $value,
    ) {}
}
