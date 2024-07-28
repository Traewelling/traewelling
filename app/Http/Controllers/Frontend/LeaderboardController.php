<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\CacheKey;
use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use stdClass;

class LeaderboardController extends Controller
{
    private static string $cacheRetentionConfigKey = 'trwl.cache.leaderboard-retention-seconds';

    public static function renderMonthlyLeaderboard(string $date): Renderable|RedirectResponse {
        if (auth()->user()?->points_enabled === false) {
            return redirect()->route('dashboard');
        }

        $date = Carbon::parse($date);

        $leaderboard = Cache::remember(
            CacheKey::getMonthlyLeaderboardKey($date),
            config(self::$cacheRetentionConfigKey),
            static fn() => LeaderboardBackend::getMonthlyLeaderboard($date)
        )->filter(function(stdClass $row) {
            return Gate::allows('view', $row->user);
        });

        return view('leaderboard.month', [
            'leaderboard' => $leaderboard,
            'date'        => Carbon::parse($date)
        ]);
    }

    public function renderLeaderboard(): Renderable|RedirectResponse {
        if (auth()->user()?->points_enabled === false) {
            return redirect()->route('dashboard');
        }

        $ttl = config(self::$cacheRetentionConfigKey);

        $usersLeaderboard = Cache::remember(
            CacheKey::LEADERBOARD_GLOBAL_POINTS,
            $ttl,
            static fn() => LeaderboardBackend::getLeaderboard()
        )->filter(function(stdClass $row) {
            return Gate::allows('view', $row->user);
        });

        $distanceLeaderboard = Cache::remember(
            CacheKey::LEADERBOARD_GLOBAL_DISTANCE,
            $ttl,
            static fn() => LeaderboardBackend::getLeaderboard(orderBy: 'distance')
        )->filter(function(stdClass $row) {
            return Gate::allows('view', $row->user);
        });

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
