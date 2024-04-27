<?php

namespace App\Listeners;

use App\Helpers\CacheKey;
use App\Models\Status;
use App\Models\User;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;


class CacheMissedListener {
    public function handle(CacheMissed $event): void {
        switch ($event->key) {
            case CacheKey::USER_CREATED:
            case CacheKey::USER_DELETED:
                $this->fetchUserData();
                break;
            case CacheKey::STATUS_CREATED:
            case CacheKey::STATUS_DELETED:
                $this->fetchStatusData();
                break;
            case CacheKey::WEBHOOK_ABSENT:
                $this->setWebhookAbsent();
                break;
            default:
                // do nothing
        }
    }
    private function setWebhookAbsent(): void {
        Cache::set(CacheKey::WEBHOOK_ABSENT, 0);
    }

    private function fetchUserData(): void {
        $count      = (new User)->count();
        $highest_id = $this->getHighestId(User::query());

        Cache::set(CacheKey::USER_CREATED, $highest_id);
        Cache::set(CacheKey::USER_DELETED, $highest_id - $count);
    }

    private function fetchStatusData(): void {
        $count      = (new Status)->count();
        $highest_id = $this->getHighestId(Status::query());

            Cache::set(CacheKey::STATUS_CREATED, $highest_id);
            Cache::set(CacheKey::STATUS_DELETED, $highest_id - $count);
    }


    private function getHighestId(Builder $query) {
        $firstOrNull = $query->orderBy("id", "desc")->first();
        if (!$firstOrNull || !isset($firstOrNull->id)) {
            return 0;
        }

        return $firstOrNull->id;
    }
}
