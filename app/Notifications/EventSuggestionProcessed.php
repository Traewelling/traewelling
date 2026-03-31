<?php

namespace App\Notifications;

use App\Enum\EventRejectionReason;
use App\Helpers\Lang;
use App\Models\Event;
use App\Models\EventSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventSuggestionProcessed extends Notification implements BaseNotification
{
    use Queueable;

    private EventSuggestion $eventSuggestion;

    private ?Event $event;

    private ?EventRejectionReason $rejectionReason;

    public function __construct(
        EventSuggestion $eventSuggestion,
        ?Event $event,
        ?EventRejectionReason $rejectionReason = null
    ) {
        $this->eventSuggestion = $eventSuggestion;
        $this->event = $event;
        $this->rejectionReason = $rejectionReason;
    }

    public function via(): array
    {
        return ['database'];
    }

    public function toArray(): array
    {
        return [
            'accepted' => $this->event !== null,
            'event' => $this->event?->only(['id', 'slug', 'name', 'checkin_start', 'checkin_end']),
            'suggestedName' => $this->eventSuggestion->name,
            'rejectionReason' => $this->rejectionReason,
        ];
    }

    public static function getLead(array $data, ?string $locale = null): string
    {
        return Lang::trans('notifications.eventSuggestionProcessed.lead', [
            'name' => $data['suggestedName'],
        ], $locale);
    }

    public static function getNotice(array $data, ?string $locale = null): ?string
    {
        if ($data['accepted']) {
            return Lang::trans('notifications.eventSuggestionProcessed.accepted', [], $locale);
        }
        if (!empty($data['rejectionReason'])) {
            return EventRejectionReason::tryFrom($data['rejectionReason'])->getReason($locale);
        }

        return EventRejectionReason::DEFAULT->getReason($locale);
    }

    public static function getLink(array $data): ?string
    {
        if (!$data['accepted']) {
            return null;
        }

        return route('event', [
            'slug' => $data['event']['slug'],
        ]);
    }
}
