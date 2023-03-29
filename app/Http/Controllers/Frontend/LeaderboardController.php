<?php

namespace App\Http\Controllers\Frontend;

use App\Enum\CacheKey;
use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Cache;

class LeaderboardController extends Controller
{
    private static string $cacheRetentionConfigKey = 'trwl.cache.leaderboard-retention-seconds';

    public static function renderMonthlyLeaderboard(string $date): Renderable {
        $date = Carbon::parse($date);

        $leaderboard = Cache::remember(
            CacheKey::LeaderboardMonth . '-for-' . $date->toISOString(),
            config(self::$cacheRetentionConfigKey),
            fn() => LeaderboardBackend::getMonthlyLeaderboard($date)
        );

        return view('leaderboard.month', [
            'leaderboard' => $leaderboard,
            'date'        => Carbon::parse($date)
        ]);
    }

    public function renderLeaderboard(): Renderable {
        $ttl = config(self::$cacheRetentionConfigKey);

        $usersLeaderboard = Cache::remember(
            CacheKey::LeaderboardGlobalPoints,
            $ttl,
            static fn() => LeaderboardBackend::getLeaderboard()
        );

        $distanceLeaderboard = Cache::remember(
            CacheKey::LeaderboardGlobalDistance,
            $ttl,
            static fn() => LeaderboardBackend::getLeaderboard(orderBy: 'distance')
        );

        $friendsLeaderboard = auth()->check()
            ? Cache::remember(
                CacheKey::getFriendsLeaderboardKey(auth()->id()),
                $ttl,
                static fn() => LeaderboardBackend::getLeaderboard(onlyFollowings: true))
            : null;

        return view('leaderboard.leaderboard', [
            'users'    => $usersLeaderboard,
            'distance' => $distanceLeaderboard,
            'friends'  => $friendsLeaderboard,
        ]);
    }
}
