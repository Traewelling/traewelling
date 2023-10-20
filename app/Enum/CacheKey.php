<?php
declare(strict_types=1);

namespace App\Enum;

use App\Models\User;
use Carbon\Carbon;

class CacheKey
{
    public const LeaderboardFriends        = 'LeaderboardFriends';
    public const LeaderboardGlobalPoints   = 'LeaderboardGlobalPoints';
    public const LeaderboardGlobalDistance = 'LeaderboardGlobalDistance';
    public const LeaderboardMonth          = 'LeaderboardMonth';
    public const StatisticsGlobal          = 'StatisticsGlobal';

    public static function getFriendsLeaderboardKey(int $userId): string {
        return self::LeaderboardFriends . '-for-' . $userId;
    }

    public static function getMonthlyLeaderboardKey(Carbon $date): string {
        return self::LeaderboardMonth . '-for-' . $date->toISOString();
    }

    public static function getGlobalStatsKey(Carbon $from, Carbon $to): string {
        return self::StatisticsGlobal . '-from-' . $from->toDateString() . '-to-' . $to->startOfHour()->toDateString();
    }

    public static function getMastodonProfileInformationKey(string $username): string {
        return "mastodon_{$username}";
    }

    public static function getYearInReviewKey(User $user, int $year): string {
        return "year-in-review-{$user->id}-{$year}";
    }
}
