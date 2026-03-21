<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Event;
use App\Models\EventSuggestion;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EventRepository
{
    /**
     * Returns three paginated event lists (future, current, past) for the admin overview.
     *
     * @return array{future: LengthAwarePaginator, current: LengthAwarePaginator, past: LengthAwarePaginator}
     */
    public function paginateForAdmin(?string $search): array
    {
        $query = Event::query();
        if ($search !== null && $search !== '') {
            $query->where('name', 'LIKE', '%' . strip_tags($search) . '%');
        }

        $today = today()->toDateString();

        return [
            'future' => $query->clone()->orderBy('checkin_start')->where('checkin_start', '>', $today)->paginate(10, pageName: 'future'),
            'current' => $query->clone()->orderBy('checkin_start')->where('checkin_start', '<=', $today)->where('checkin_end', '>=', $today)->paginate(10, pageName: 'current'),
            'past' => $query->clone()->where('checkin_end', '<', $today)->paginate(10, pageName: 'past'),
        ];
    }

    public function paginateForAdminCursor(?string $search, ?string $status): CursorPaginator
    {
        $today = today()->toDateString();
        $query = Event::with(['station'])->orderBy('checkin_start');

        if ($search !== null && $search !== '') {
            $query->where('name', 'LIKE', '%' . strip_tags($search) . '%');
        }

        match ($status) {
            'future' => $query->where('checkin_start', '>', $today),
            'current' => $query->where('checkin_start', '<=', $today)->where('checkin_end', '>=', $today),
            'past' => $query->where('checkin_end', '<', $today)->orderByDesc('checkin_start'),
            default => null,
        };

        return $query->cursorPaginate(25);
    }

    public function paginateOpenSuggestions(): CursorPaginator
    {
        return EventSuggestion::with(['user', 'station'])
            ->where('processed', false)
            ->where('end', '>=', today()->toDateString())
            ->orderBy('begin')
            ->cursorPaginate(25);
    }

    /**
     * Returns all events that overlap with the suggestion's date range,
     * sorted by name similarity (descending).
     */
    public function findParallelEventsWithSimilarity(EventSuggestion $suggestion): Collection
    {
        $begin = $suggestion->begin->toDateString();
        $end = $suggestion->end->toDateString();

        $events = Event::where([['checkin_start', '>=', $begin], ['checkin_end', '<=', $end]])
            ->orWhere(fn ($q) => $q->where([['checkin_start', '<=', $begin], ['checkin_end', '>=', $begin]]))
            ->orWhere(fn ($q) => $q->where([['checkin_start', '<=', $end], ['checkin_end', '>=', $end]]))
            ->get();

        $events->each(function (Event $event) use ($suggestion): void {
            similar_text($event->name, $suggestion->name, $perc);
            $event->similarity = $perc;
        });

        return $events->sortByDesc('similarity');
    }
}
