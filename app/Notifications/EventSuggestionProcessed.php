<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventSuggestion;
use Illuminate\Bus\Queueable;

class EventSuggestionProcessed extends BaseNotification
{
    use Queueable;

    private EventSuggestion $eventSuggestion;
    private ?Event          $event;

    public function __construct(EventSuggestion $eventSuggestion, ?Event $event) {
        $this->eventSuggestion = $eventSuggestion;
        $this->event           = $event;
    }

    public function via(): array {
        return ['database'];
    }

    public function toArray(): array {
        return [
            'accepted'      => $this->event !== null,
            'event'         => $this->event?->only(['id', 'slug', 'name', 'begin', 'end']),
            'suggestedName' => $this->eventSuggestion->name,
        ];
    }

    public static function getLead(array $data): string {
        return __('notifications.eventSuggestionProcessed.lead', [
            'name' => $data['suggestedName'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.eventSuggestionProcessed.' . ($data['accepted'] ? 'accepted' : 'denied'));
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
