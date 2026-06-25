<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enum\TravelType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CacheKey
{
    // static keys
    public const string STATUS_CREATED = 'monitoring-counter-StatusCreated';

    public const string STATUS_DELETED = 'monitoring-counter-StatusDeleted';

    public const string USER_CREATED = 'monitoring-counter-UserCreated';

    public const string USER_DELETED = 'monitoring-counter-UserDeleted';

    public const string WEBHOOK_ABSENT = 'monitoring-counter-WebhookAbsent';

    public const string LEADERBOARD_GLOBAL_POINTS = 'LeaderboardGlobalPoints';

    public const string LEADERBOARD_GLOBAL_DISTANCE = 'LeaderboardGlobalDistance';

    private const string API_RESPONSE_TIME_SUM = 'prom_api_rt_sum_%d';

    private const string API_RESPONSE_TIME_COUNT = 'prom_api_rt_count_%d';

    private const string API_RESPONSE_CODE = 'prom_api_rc_%d_%d';

    // dynamic keys
    private const string LEADERBOARD_FRIENDS = 'LeaderboardFriends';

    private const string LEADERBOARD_MONTH = 'LeaderboardMonth';

    private const string STATISTICS_GLOBAL = 'StatisticsGlobal';

    private const string HAFAS_TRIP = '_HafasTrip_%s_%s';

    private const string HAFAS_STATIONS = '_HafasStations';

    private const string HAFAS_DEPARTURES = '_HafasDepartures_%d_%s_%s_%s';

    private const string HAFAFS_STATION_RIL = '_HafasStationRil';

    private const string HAFAS_STATIONS_FUZZY = '_HafasStationsFuzzy';

    private const string HAFAS_CACHE_HIT = '_HafasCacheHit_%s';

    private const string HAFAS_CACHE_SET = '_HafasCacheSet_%s';

    private const string ICS_USER_MONTHLY = 'IcsUserMonthly_%s_%s';

    private const string REROUTE_POLYLINE_JOB = 'ReroutePolylineJob_%s';

    // formatting keys
    private const string FOR = '%s-for-%s';

    private const string FROM_TO = '%s-from-%s-to-%s';

    public static function getReroutePolylineJobKey(int $tripId): string
    {
        return sprintf(self::REROUTE_POLYLINE_JOB, $tripId);
    }

    public static function getHafasCacheHitKey(string $key): string
    {
        $key = str_replace('monitoring-counter-', '', $key);

        return sprintf(self::HAFAS_CACHE_HIT, $key);
    }

    public static function getHafasCacheSetKey(string $key): string
    {
        $key = str_replace('monitoring-counter-', '', $key);

        return sprintf(self::HAFAS_CACHE_SET, $key);
    }

    public static function getHafasTripKey(string $tripId, string $lineName): string
    {
        $tripId = sha1($tripId);

        return sprintf(self::HAFAS_TRIP, $tripId, $lineName);
    }

    public static function getHafasStationsKey(string $query): string
    {
        return sprintf(self::FOR, self::HAFAS_STATIONS, $query);
    }

    public static function getHafasDeparturesKey(string $stationId, Carbon $when, bool $localtime, ?TravelType $type): string
    {
        return sprintf(
            self::HAFAS_DEPARTURES,
            $stationId,
            $when->toTimeString(),
            $localtime ? 'local' : 'utc',
            $type ? $type->value : 'all'
        );
    }

    public static function getHafasByRilIdentifierKey(string $rilIdentifier): string
    {
        return sprintf(self::FOR, self::HAFAFS_STATION_RIL, $rilIdentifier);
    }

    public static function getHafasStationsFuzzyKey(string $rilIdentifier): string
    {
        return sprintf(self::FOR, self::HAFAS_STATIONS_FUZZY, $rilIdentifier);
    }

    public static function getFriendsLeaderboardKey(int $userId): string
    {
        return sprintf(self::FOR, self::LEADERBOARD_FRIENDS, $userId);
    }

    public static function getMonthlyLeaderboardKey(Carbon $date): string
    {
        return sprintf(self::FOR, self::LEADERBOARD_MONTH, $date->toISOString());
    }

    public static function getGlobalStatsKey(Carbon $from, Carbon $to): string
    {
        return sprintf(
            self::FROM_TO,
            self::STATISTICS_GLOBAL,
            $from->toDateString(),
            $to->startOfHour()->toDateString()
        );
    }

    public static function getMastodonProfileInformationKey(User $user): string
    {
        return sprintf('mastodon_%s', $user->id);
    }

    public static function getMastodonProfileErrorKey(User $user): string
    {
        return sprintf('mastodon_error_%s', $user->id);
    }

    public static function getYearInReviewKey(User $user, int $year): string
    {
        return sprintf('year-in-review-%s-%s', $user->id, $year);
    }

    public static function getAccountDeletionNotificationTwoWeeksBeforeKey(User $user): string
    {
        return sprintf('account-deletion-notification-two-weeks-before-%s', $user->id);
    }

    public static function getIcsUserMonthlyKey(User|int $user, Carbon $date): string
    {
        $userId = $user instanceof User ? $user->id : $user;

        return sprintf(self::ICS_USER_MONTHLY, $userId, $date->format('Y-m'));
    }

    public static function forgetIcsUserMonthly(User|int $user, Carbon $date): void
    {
        Cache::forget(self::getIcsUserMonthlyKey($user, $date));
    }

    public static function increment(string $key): void
    {
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1);
        }
    }

    public static function getPasswordResetThrottleKey(?User $user, string $emailFallback = ''): string
    {
        return 'password_reset_' . ($user?->id ?? sha1($emailFallback));
    }

    public static function getApiResponseTimeSumKey(int $minuteBucket): string
    {
        return sprintf(self::API_RESPONSE_TIME_SUM, $minuteBucket);
    }

    public static function getApiResponseTimeCountKey(int $minuteBucket): string
    {
        return sprintf(self::API_RESPONSE_TIME_COUNT, $minuteBucket);
    }

    public static function getApiResponseCodeKey(int $minuteBucket, int $statusCode): string
    {
        return sprintf(self::API_RESPONSE_CODE, $minuteBucket, $statusCode);
    }
}
