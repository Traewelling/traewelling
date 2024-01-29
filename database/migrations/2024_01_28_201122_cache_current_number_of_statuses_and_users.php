<?php

use App\Enum\CacheKey;
use App\Enum\MonitoringCounter;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        {
            $count      = Status::count();
            $highest_id = $this->getHighestId(Status::query());

            Cache::set(CacheKey::getMonitoringCounterKey(MonitoringCounter::StatusCreated), $highest_id);
            Cache::set(CacheKey::getMonitoringCounterKey(MonitoringCounter::StatusDeleted), $highest_id - $count);
        }

        {
            $count      = User::count();
            $highest_id = $this->getHighestId(User::query());

            Cache::set(CacheKey::getMonitoringCounterKey(MonitoringCounter::UserCreated), $highest_id);
            Cache::set(CacheKey::getMonitoringCounterKey(MonitoringCounter::UserDeleted), $highest_id - $count);
        }
    }

    private function getHighestId(Builder $query) {
        $firstOrNull = $query->orderBy("id", "desc")->first();
        if (!$firstOrNull || !isset($firstOrNull->id)) {
            return 0;
        }

        return $firstOrNull->id;
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Cache::forget(CacheKey::getMonitoringCounterKey(MonitoringCounter::StatusCreated));
        Cache::forget(CacheKey::getMonitoringCounterKey(MonitoringCounter::StatusDeleted));

        Cache::forget(CacheKey::getMonitoringCounterKey(MonitoringCounter::UserCreated));
        Cache::forget(CacheKey::getMonitoringCounterKey(MonitoringCounter::UserDeleted));
    }
};
