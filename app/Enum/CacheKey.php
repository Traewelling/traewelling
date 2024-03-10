<?php
declare(strict_types=1);

namespace App\Enum;

use App\Models\User;
use Carbon\Carbon;

enum CacheKey: string
{
    case LeaderboardFriends        = 'LeaderboardFriends';
    case LeaderboardGlobalPoints   = 'LeaderboardGlobalPoints';
    case LeaderboardGlobalDistance = 'LeaderboardGlobalDistance';
    case LeaderboardMonth          = 'LeaderboardMonth';
    case StatisticsGlobal          = 'StatisticsGlobal';

    public static function getFriendsLeaderboardKey(int $userId): string {
        return self::LeaderboardFriends->value . '-for-' . $userId;
    }

    public static function getMonthlyLeaderboardKey(Carbon $date): string {
        return self::LeaderboardMonth->value . '-for-' . $date->toISOString();
    }

    public static function getGlobalStatsKey(Carbon $from, Carbon $to): string {
        return self::StatisticsGlobal->value . '-from-' . $from->toDateString() . '-to-' . $to->startOfHour()->toDateString();
    }

    public static function getMastodonProfileInformationKey(User $user): string {
        return "mastodon_{$user->id}";
    }

    public static function getYearInReviewKey(User $user, int $year): string {
        return "year-in-review-{$user->id}-{$year}";
    }

    public static function getAccountDeletionNotificationTwoWeeksBeforeKey(User $user): string {
        return "account-deletion-notification-two-weeks-before-{$user->id}";
    }

    public static function getMonitoringCounterKey(MonitoringCounter $counter): string {
        return "monitoring-counter-{$counter->value}";
    }
}
