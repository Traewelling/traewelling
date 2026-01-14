<?php

namespace App\Dto\Internal;

readonly class IcsExportStatus
{
    /**
     * @param  IcsExportStatusTag[]  $statusTags
     */
    public function __construct(
        public string $originName,
        public string $destinationName,
        public string $checkinId,
        public ?string $createdAt,
        public string $journeyNumber,
        public string $lineName,
        public ?string $departure,
        public ?string $arrival,
        public array $statusTags,
        public ?string $body,
        public ?string $emoji,
    ) {}
}
