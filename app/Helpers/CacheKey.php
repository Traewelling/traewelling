<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\User;
use Carbon\Carbon;

class CacheKey
{
    // static keys
    public const string STATUS_CREATED            = 'monitoring-counter-StatusCreated';
    public const string STATUS_DELETED            = 'monitoring-counter-StatusDeleted';
    public const string USER_CREATED              = 'monitoring-counter-UserCreated';
    public const string USER_DELETED              = 'monitoring-counter-UserDeleted';
    public const string WEBHOOK_ABSENT            = 'monitoring-counter-WebhookAbsent';
    public const string LeaderboardGlobalPoints   = 'LeaderboardGlobalPoints';
    public const string LeaderboardGlobalDistance = 'LeaderboardGlobalDistance';

    // dynamic keys
    private const string LeaderboardFriends = 'LeaderboardFriends';
    private const string LeaderboardMonth   = 'LeaderboardMonth';
    private const string StatisticsGlobal   = 'StatisticsGlobal';

    // formatting keys
    private const string FOR                = '%s-for-%s';
    private const string FOR_TO             = '%s-for-%s-to-%s';

    public static function getFriendsLeaderboardKey(int $userId): string {
        return sprintf(self::FOR, self::LeaderboardFriends, $userId);
    }

    public static function getMonthlyLeaderboardKey(Carbon $date): string {
        return sprintf(self::FOR, self::LeaderboardMonth, $date->toISOString());
    }

    public static function getGlobalStatsKey(Carbon $from, Carbon $to): string {
        return sprintf(self::FOR_TO, self::StatisticsGlobal, $from->toISOString(), $to->toISOString());
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
}
