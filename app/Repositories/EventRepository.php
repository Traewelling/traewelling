<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\EventSuggestion;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
    $query = Event::with(['station']);

    if ($search !== null && $search !== '') {
        $query->where('name', 'LIKE', '%' . strip_tags($search) . '%');
    }

    $query = match ($status) {
        // Show upcoming events in chronological order (earliest first)
        'future' => $query->where('checkin_start', '>', $today)->orderBy('checkin_start', 'asc'),
        // Show current events in chronological order (earliest first)
        'current' => $query->where('checkin_start', '<=', $today)->where('checkin_end', '>=', $today)->orderBy('checkin_start', 'asc'),
        // Show past events in reverse chronological order (most recent first)
        'past' => $query->where('checkin_end', '<', $today)->orderByDesc('checkin_start'),
        // Default ordering for unspecified status
        default => $query->orderBy('checkin_start', 'asc'),
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

    private function forTimestampQuery(Carbon $timestamp, bool $showUpcoming = false): Builder
    {
        $date = $timestamp->toDateString();
        $query = Event::with(['station'])->where('checkin_end', '>=', $date)->orderBy('checkin_start', 'asc');
        if (!$showUpcoming) {
            $query->where('checkin_start', '<=', $date);
        }

        return $query;
    }

    /**
     * @deprecated remove, once frontend is removed. Combine paginateForTimestamp with forTimestampQuery
     */
    public function paginateForFrontend(Carbon $timestamp): LengthAwarePaginator
    {
        return $this->forTimestampQuery($timestamp, true)->paginate(15);
    }

    public function paginateForTimestamp(Carbon $timestamp, bool $showUpcoming = false): AnonymousResourceCollection
    {
        return EventResource::collection($this->forTimestampQuery($timestamp, $showUpcoming)->with(['station'])->paginate(100));
    }

    public function paginateForPeriod(Carbon $start, Carbon $end): AnonymousResourceCollection
    {
        $query = Event::with(['station'])
            ->where('checkin_start', '>=', $start)
            ->where('checkin_start', '<=', $end)
            ->orderBy('event_start', 'asc');

        return EventResource::collection($query->simplePaginate(perPage: 100));
    }

    public function getBySlug(string $slug): Event
    {
        return Event::where('slug', '=', $slug)->firstOrFail();
    }

    public function getById(int $id): Event
    {
        return Event::find($id);
    }
}
