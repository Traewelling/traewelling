<?php

declare(strict_types=1);

namespace App\Services\Checkin;

use App\Models\StatusTag;
use App\Models\User;
use Illuminate\Support\Collection;

class TagSuggestionService
{
    private const int RECENT_FETCH_LIMIT = 50;

    private const int SUGGESTION_PER_GROUP = 3;

    private const int FREQUENT_DAYS = 3;

    private const int FREQUENT_MIN_COUNT = 2;

    public function getSuggestions(User $user): Collection
    {
        $recent = $this->getRecentTags($user);
        $frequent = $this->getFrequentTags($user);

        $seen = $recent->map(fn ($t) => $t->key . ':' . $t->value)->all();
        $suggestions = $recent->values();

        foreach ($frequent as $tag) {
            $fingerprint = $tag->key . ':' . $tag->value;
            if (!in_array($fingerprint, $seen, true)) {
                $seen[] = $fingerprint;
                $suggestions->push($tag);
            }
        }

        return $suggestions;
    }

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
