<?php

namespace App\Notifications;

use App\Enum\EventRejectionReason;
use App\Models\Event;
use App\Models\EventSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EventSuggestionProcessed extends Notification implements BaseNotification
{
    use Queueable;

    private EventSuggestion      $eventSuggestion;
    private ?Event               $event;
    private ?EventRejectionReason $reason;

    public function __construct(EventSuggestion $eventSuggestion, ?Event $event, ?EventRejectionReason $reason = null) {
        $this->eventSuggestion = $eventSuggestion;
        $this->event           = $event;
        $this->reason          = $reason;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'accepted'      => $this->event !== null,
            'event'         => $this->event?->only(['id', 'slug', 'name', 'begin', 'end']),
            'suggestedName' => $this->eventSuggestion->name,
            'reason'        => $this->reason,
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.eventSuggestionProcessed.lead', [
            'name' => $data['suggestedName'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        if ($data['accepted']) {
            return __('notifications.eventSuggestionProcessed.accepted');
        }
        if (!empty($data['reason'])) {
            return __(EventRejectionReason::tryFrom($data['reason'])->getReason());
        }
        return __(EventRejectionReason::DEFAULT->getReason());
    }

    public static function getLink(array $data): ?string {
        if (!$data['accepted']) {
            return null;
        }
        return route('statuses.byEvent', [
            'eventSlug' => $data['event']['slug'],
        ]);
    }
}
