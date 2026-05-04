<?php

declare(strict_types=1);

namespace App\Dto\Internal;

use App\Models\Checkin;

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

    public static function fromCheckin(Checkin $checkin, bool $useEmojis = true): self
    {
        $tags = [];
        foreach ($checkin->status->tags as $tag) {
            $tags[] = new IcsExportStatusTag(key: $tag->key, value: $tag->value);
        }

        return new self(
            originName: $checkin->originStopover->station->name,
            destinationName: $checkin->destinationStopover->station->name,
            checkinId: (string) $checkin->id,
            createdAt: $checkin->created_at?->toIso8601ZuluString(),
            journeyNumber: (string) ($checkin->trip->journey_number ?? ''),
            lineName: $checkin->trip->linename,
            departure: $checkin->display_departure->time?->toIso8601ZuluString(),
            arrival: $checkin->display_arrival->time?->toIso8601ZuluString(),
            statusTags: $tags,
            body: $checkin->status->body,
            emoji: $useEmojis ? $checkin->trip?->category?->getEmoji() : null,
        );
    }
}
