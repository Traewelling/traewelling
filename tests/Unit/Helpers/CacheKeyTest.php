<?php

namespace Tests\Unit\Helpers;

use App\Helpers\CacheKey;
use App\Models\User;
use Carbon\Carbon;
use Tests\Unit\UnitTestCase;

class CacheKeyTest extends UnitTestCase
{

    public function testGetGlobalStatsKey(): void {
        $from      = Carbon::create(2012, 2, 12, 15, 32, 45);
        $to        = $from->clone()->addQuarter();
        $second_to = $to->clone()->addMinutes(5);

        $expected = "StatisticsGlobal-from-2012-02-12-to-2012-05-12";
        $this->assertEquals($expected, CacheKey::getGlobalStatsKey($from, $to));
        $this->assertEquals($expected, CacheKey::getGlobalStatsKey($from, $second_to));
    }

    public function testMastodonKey(): void {
        $user = User::factory()->make();
        $expected = "mastodon_{$user->id}";
        $this->assertEquals($expected, CacheKey::getMastodonProfileInformationKey($user));
    }

    public function testYearInReviewKey(): void {
        $user = User::factory()->make();
        $year = 2021;
        $expected = "year-in-review-{$user->id}-{$year}";
        $this->assertEquals($expected, CacheKey::getYearInReviewKey($user, $year));
    }

    public function testAccountDeletionNotificationTwoWeeksBeforeKey(): void {
        $user = User::factory()->make();
        $expected = "account-deletion-notification-two-weeks-before-{$user->id}";
        $this->assertEquals($expected, CacheKey::getAccountDeletionNotificationTwoWeeksBeforeKey($user));
    }

    public function testGetFriendsLeaderboardKey(): void {
        $userId = 123;
        $expected = "LeaderboardFriends-for-{$userId}";
        $this->assertEquals($expected, CacheKey::getFriendsLeaderboardKey($userId));
    }

    public function testGetMonthlyLeaderboardKey(): void {
        $date = Carbon::create(2012, 2, 12, 15, 32, 45);
        $expected = "LeaderboardMonth-for-2012-02-12T15:32:45.000000Z";
        $this->assertEquals($expected, CacheKey::getMonthlyLeaderboardKey($date));
    }
}
