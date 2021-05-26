<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;

class LeaderboardController extends Controller
{
    public static function renderMonthlyLeaderboard(string $date): Renderable {
        $date = Carbon::parse($date);
        return view('leaderboard.month', [
            'leaderboard' => LeaderboardBackend::getMonthlyLeaderboard($date),
            'date'        => Carbon::parse($date)
        ]);
    }

    public function renderLeaderboard(): Renderable {
        return view('leaderboard.leaderboard', [
            'users'    => LeaderboardBackend::getLeaderboard(),
            'friends'  => auth()->check() ? LeaderboardBackend::getLeaderboard(onlyFollowings: true) : null,
            'distance' => LeaderboardBackend::getLeaderboard(orderBy: 'distance')
        ]);
    }
}
