<?php

declare(strict_types=1);

namespace App\Dto\Internal;

readonly class ProviderPolylineImportResult
{
    public function __construct(
        public int $created = 0,
        public int $reused = 0,
        public int $skipped = 0,
        public ?string $abortReason = null,
    ) {}

    public static function aborted(string $reason): self
    {
        return new self(abortReason: $reason);
    }

    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'reused' => $this->reused,
            'skipped' => $this->skipped,
            'abort_reason' => $this->abortReason,
        ];
    }
}
