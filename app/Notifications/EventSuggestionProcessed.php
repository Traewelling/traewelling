<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventSuggestion;
use Illuminate\Bus\Queueable;
use JetBrains\PhpStorm\ArrayShape;

class EventSuggestionProcessed extends BaseNotification
{
    use Queueable;

    private EventSuggestion $eventSuggestion;
    private ?Event          $event;

    public function __construct(EventSuggestion $eventSuggestion, ?Event $event) {
        $this->eventSuggestion = $eventSuggestion;
        $this->event           = $event;
    }

    public static function render(mixed $notification): ?string {
        return view("includes.notification", [
            'color'           => 'neutral',
            'icon'            => 'fa-regular fa-calendar',
            'lead'            => __('notifications.eventSuggestionProcessed.lead', ["name" => $notification->data["name"]]),
            'link'            => $notification->data["accepted"] ? route('statuses.byEvent', [
                'eventSlug' => $notification->data["event"]["slug"]
            ]) : "#",
            'notice'          => __('notifications.eventSuggestionProcessed.' . ($notification->data["accepted"] ? "accepted" : "denied")),
            'date_for_humans' => $notification->created_at->diffForHumans(),
            'read'            => $notification->read_at != null,
            'notificationId'  => $notification->id
        ])->render();
    }

    public function via($notifiable): array {
        return ['database'];
    }

    #[ArrayShape(['accepted' => 'bool', 'event' => Event::class, 'name' => 'string'])]
    public function toArray(): array {
        return [
            'accepted' => $this->event !== null,
            'event'    => $this->event,
            'name'     => $this->eventSuggestion->name,
        ];
    }

    public static function getIcon(): string {
        return 'fa-regular fa-calendar';
    }

    public static function getLead(array $data): string {
        return __('notifications.eventSuggestionProcessed.lead', [
            'name' => $data['name'],
        ]);
    }

    public static function getNotice(array $data): ?string {
        return __('notifications.eventSuggestionProcessed.' . ($data['accepted'] ? "accepted" : "denied"));
    }

    public static function getLink(array $data): ?string {
        if (!$data['accepted']) {
            return null;
        }
        return route('statuses.byEvent', [
            'eventSlug' => $data['slug'], //TODO: Save slug...
        ]);
    }
}
