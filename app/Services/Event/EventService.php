<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Enum\ContributionActionType;
use App\Enum\EventRejectionReason;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Station;
use App\Models\User;
use App\Notifications\EventSuggestionProcessed;
use App\Services\Contribution\ContributionXPService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventService
{
    public function suggestEvent(
        User $user,
        string $name,
        Carbon $begin,
        Carbon $end,
        ?Station $station = null,
        ?string $url = null,
        ?string $host = null,
        ?string $hashtag = null,
    ): EventSuggestion {
        if ($hashtag !== null && str_starts_with($hashtag, '#')) {
            $hashtag = substr($hashtag, 1);
        }

        $eventSuggestion = EventSuggestion::create([
            'user_id' => $user->id,
            'name' => $name,
            'begin' => $begin->toDateString(),
            'end' => $end->toDateString(),
            'station_id' => $station?->id,
            'url' => $url,
            'host' => $host,
            'hashtag' => $hashtag,
        ]);

        return $eventSuggestion;
    }

    public function createSlugFromName(string $name): string
    {
        $slug = Str::slug($name, '_');
        $i = '';

        while (Event::where('slug', $slug . $i)->exists()) {
            $i = $i === '' ? 1 : $i + 1;
        }

        return $i !== '' ? $slug . $i : $slug;
    }

    public function createEvent(
        string $name,
        ?string $hashtag,
        ?string $host,
        ?Station $station,
        Carbon $checkinStart,
        Carbon $checkinEnd,
        ?Carbon $eventStart,
        ?Carbon $eventEnd,
        ?string $url,
        User $acceptedBy,
    ): Event {
        return Event::create([
            'name' => $name,
            'slug' => $this->createSlugFromName($name),
            'hashtag' => $hashtag,
            'host' => $host,
            'station_id' => $station?->id,
            'checkin_start' => $checkinStart->toDateString(),
            'checkin_end' => $checkinEnd->toDateString(),
            'event_start' => $eventStart?->toDateString(),
            'event_end' => $eventEnd?->toDateString(),
            'url' => $url,
            'accepted_by' => $acceptedBy->id,
        ]);
    }

    public function updateEvent(
        Event $event,
        string $name,
        ?string $hashtag,
        ?string $host,
        ?Station $station,
        Carbon $checkinStart,
        Carbon $checkinEnd,
        ?Carbon $eventStart,
        ?Carbon $eventEnd,
        ?string $url,
    ): void {
        $event->update([
            'name' => $name,
            'hashtag' => $hashtag,
            'host' => $host,
            'station_id' => $station?->id,
            'checkin_start' => $checkinStart->toDateString(),
            'checkin_end' => $checkinEnd->toDateString(),
            'event_start' => $eventStart?->toDateString(),
            'event_end' => $eventEnd?->toDateString(),
            'url' => $url,
        ]);
    }

    public function acceptSuggestion(
        EventSuggestion $suggestion,
        ?Station $station,
        User $acceptedBy,
        string $name,
        ?string $hashtag,
        ?string $host,
        Carbon $checkinStart,
        Carbon $checkinEnd,
        ?Carbon $eventStart,
        ?Carbon $eventEnd,
        ?string $url,
    ): Event {
        $event = $this->createEvent(
            name: $name,
            hashtag: $hashtag,
            host: $host,
            station: $station,
            checkinStart: $checkinStart,
            checkinEnd: $checkinEnd,
            eventStart: $eventStart,
            eventEnd: $eventEnd,
            url: $url,
            acceptedBy: $acceptedBy,
        );

        $suggestion->update(['processed' => true]);

        $suggestion->user->notify(new EventSuggestionProcessed($suggestion, $event));

        if ($suggestion->user !== null) {
            ContributionXPService::grantXP(
                user: $suggestion->user,
                xpChange: ContributionXPService::getXPForEventApproval(),
                action: ContributionActionType::EVENT_SUGGESTED,
                entityType: 'event_suggestion',
                entityId: $suggestion->id,
                note: 'Event approved: ' . $event->name,
            );
        }

        return $event;
    }

    public function denySuggestion(EventSuggestion $suggestion, EventRejectionReason $reason): void
    {
        $suggestion->update(['processed' => true]);

        $suggestion->user->notify(new EventSuggestionProcessed($suggestion, null, $reason));

        if ($suggestion->user !== null && $reason->getXPChange() !== 0) {
            ContributionXPService::grantXP(
                user: $suggestion->user,
                xpChange: $reason->getXPChange(),
                action: ContributionActionType::EVENT_SUGGESTED,
                entityType: 'event_suggestion',
                entityId: $suggestion->id,
                note: 'Event denied: ' . $reason->value,
            );
        }
    }
}
