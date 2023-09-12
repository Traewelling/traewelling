<?php
declare(strict_types=1);

namespace App\Enum;

use Carbon\Carbon;

class CacheKey
{
    public const LeaderboardFriends        = 'LeaderboardFriends';
    public const LeaderboardGlobalPoints   = 'LeaderboardGlobalPoints';
    public const LeaderboardGlobalDistance = 'LeaderboardGlobalDistance';
    public const LeaderboardMonth          = 'LeaderboardMonth';
    public const SchedulerCanary           = 'scheduler-canary';
    public const StatisticsGlobal          = 'StatisticsGlobal';

    public static function getFriendsLeaderboardKey(int $userId): string {
        return self::LeaderboardFriends . '-for-' . $userId;
    }

    public static function getGlobalStatsKey(Carbon $from, Carbon $to): string {
        return self::StatisticsGlobal . '-from-' . $from->toDateString() . '-to-' . $to->startOfHour()->toDateString();
    }
}
