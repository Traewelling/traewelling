<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheKey
{
    // static keys
    public const string STATUS_CREATED              = 'monitoring-counter-StatusCreated';
    public const string STATUS_DELETED              = 'monitoring-counter-StatusDeleted';
    public const string USER_CREATED                = 'monitoring-counter-UserCreated';
    public const string USER_DELETED                = 'monitoring-counter-UserDeleted';
    public const string WEBHOOK_ABSENT              = 'monitoring-counter-WebhookAbsent';
    public const string LEADERBOARD_GLOBAL_POINTS   = 'LeaderboardGlobalPoints';
    public const string LEADERBOARD_GLOBAL_DISTANCE = 'LeaderboardGlobalDistance';

    // dynamic keys
    private const string LEADERBOARD_FRIENDS = 'LeaderboardFriends';
    private const string LEADERBOARD_MONTH   = 'LeaderboardMonth';
    private const string STATISTICS_GLOBAL   = 'StatisticsGlobal';

    // formatting keys
    private const string FOR                 = '%s-for-%s';
    private const string FROM_TO             = '%s-from-%s-to-%s';

    public static function getFriendsLeaderboardKey(int $userId): string {
        return sprintf(self::FOR, self::LEADERBOARD_FRIENDS, $userId);
    }

    public static function getMonthlyLeaderboardKey(Carbon $date): string {
        return sprintf(self::FOR, self::LEADERBOARD_MONTH, $date->toISOString());
    }

    public static function getGlobalStatsKey(Carbon $from, Carbon $to): string {
        return sprintf(
            self::FROM_TO,
            self::STATISTICS_GLOBAL,
            $from->toDateString(),
            $to->startOfHour()->toDateString()
        );
    }

    public static function getMastodonProfileInformationKey(User $user): string {
        return sprintf("mastodon_%s", $user->id);
    }

    public static function getYearInReviewKey(User $user, int $year): string {
        return sprintf("year-in-review-%s-%s", $user->id, $year);
    }

    public static function getAccountDeletionNotificationTwoWeeksBeforeKey(User $user): string {
        return sprintf("account-deletion-notification-two-weeks-before-%s", $user->id);
    }

    public static function increment(string $key): void {
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1);
        }
    }
}
