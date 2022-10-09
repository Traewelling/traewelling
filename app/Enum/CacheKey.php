<?php
declare(strict_types=1);

namespace App\Enum;

class CacheKey
{
    public const LeaderboardFriends        = "LeaderboardFriends";
    public const LeaderboardGlobalPoints   = "LeaderboardGlobalPoints";
    public const LeaderboardGlobalDistance = "LeaderboardGlobalDistance";
    public const LeaderboardMonth          = "LeaderboardMonth";

    public static function getFriendsLeaderboardKey(int $userId): string {
        return self::LeaderboardFriends . '-for-' . $userId;
    }
}
