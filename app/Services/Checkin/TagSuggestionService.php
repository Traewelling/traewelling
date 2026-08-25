<?php

declare(strict_types=1);

namespace App\Services\Checkin;

use App\Dto\StatusTagSuggestionDto;
use App\Enum\StatusTagKey;
use App\Enum\TagSuggestionSource;
use App\Models\StatusTag;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class TagSuggestionService
{
    private const int RECENT_FETCH_LIMIT = 50;

    private const int SUGGESTION_PER_GROUP = 3;

    private const int FREQUENT_DAYS = 3;

    private const int FREQUENT_MIN_COUNT = 2;

    private const int TRIP_FETCH_LIMIT = 50;

    /**
     * Tags that describe the trip itself and are therefore worth suggesting to
     * everybody checking into the same trip.
     */
    private const array TRIP_SPECIFIC_KEYS = [
        StatusTagKey::VEHICLE_NUMBER,
        StatusTagKey::JOURNEY_NUMBER,
        StatusTagKey::LOCOMOTIVE_CLASS,
    ];

    /**
     * @return Collection<int, StatusTagSuggestionDto>
     */
    public function getSuggestions(User $user, ?Trip $trip = null): Collection
    {
        $suggestions = new Collection();
        $seen = [];

        $append = static function (Collection $tags, TagSuggestionSource $source) use (&$seen, $suggestions): void {
            foreach ($tags as $tag) {
                $suggestion = new StatusTagSuggestionDto($tag->key, $tag->value, $source);
                if (in_array($suggestion->fingerprint(), $seen, true)) {
                    continue;
                }
                $seen[] = $suggestion->fingerprint();
                $suggestions->push($suggestion);
            }
        };

        if ($trip !== null) {
            $append($this->getTripTags($user, $trip), TagSuggestionSource::TRIP);
        }
        $append($this->getRecentTags($user), TagSuggestionSource::RECENT);
        $append($this->getFrequentTags($user), TagSuggestionSource::FREQUENT);

        return $suggestions->values();
    }

    /**
     * Trip specific tags that other users already added to the same trip. Both the
     * status and the tag itself have to be visible for the given user: a public tag
     * on a status the user cannot see must not be suggested.
     *
     * @return Collection<int, StatusTag>
     */
    private function getTripTags(User $user, Trip $trip): Collection
    {
        return StatusTag::query()
            ->join('statuses', 'status_tags.status_id', '=', 'statuses.id')
            ->join('train_checkins', 'train_checkins.status_id', '=', 'statuses.id')
            ->where('train_checkins.trip_id', $trip->trip_id)
            ->whereIn('status_tags.key', array_map(static fn (StatusTagKey $key) => $key->value, self::TRIP_SPECIFIC_KEYS))
            ->with(['status.user'])
            ->orderByDesc('status_tags.created_at')
            ->limit(self::TRIP_FETCH_LIMIT)
            ->select('status_tags.*')
            ->get()
            ->filter(static fn (StatusTag $tag) => Gate::forUser($user)->allows('view', $tag->status)
                                                   && Gate::forUser($user)->allows('view', $tag))
            ->unique(static fn (StatusTag $tag) => $tag->key . ':' . $tag->value)
            ->take(self::SUGGESTION_PER_GROUP);
    }

    /**
     * @return Collection<int, StatusTag>
     */
    private function getRecentTags(User $user): Collection
    {
        return StatusTag::query()
            ->join('statuses', 'status_tags.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', $user->id)
            ->orderByDesc('status_tags.created_at')
            ->limit(self::RECENT_FETCH_LIMIT)
            ->get(['status_tags.key', 'status_tags.value', 'status_tags.visibility'])
            ->unique(fn ($tag) => $tag->key . ':' . $tag->value)
            ->take(self::SUGGESTION_PER_GROUP);
    }

    /**
     * @return Collection<int, StatusTag>
     */
    private function getFrequentTags(User $user): Collection
    {
        return StatusTag::query()
            ->join('statuses', 'status_tags.status_id', '=', 'statuses.id')
            ->where('statuses.user_id', $user->id)
            ->where('status_tags.created_at', '>=', now()->subDays(self::FREQUENT_DAYS))
            ->selectRaw('status_tags.key, status_tags.value, MAX(status_tags.visibility) as visibility, COUNT(*) as usage_count')
            ->groupBy('status_tags.key', 'status_tags.value')
            ->having('usage_count', '>=', self::FREQUENT_MIN_COUNT)
            ->orderByDesc('usage_count')
            ->limit(self::SUGGESTION_PER_GROUP)
            ->get();
    }
}
