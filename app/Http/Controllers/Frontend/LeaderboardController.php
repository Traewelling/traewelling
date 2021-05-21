<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;

class LeaderboardController extends Controller
{
    public function renderLeaderboard(): Renderable {
        return view('leaderboard.leaderboard', [
            'users'    => LeaderboardBackend::getLeaderboard(),
            'friends'  => auth()->check() ? LeaderboardBackend::getLeaderboard(onlyFollowings: true) : null,
            'distance' => LeaderboardBackend::getLeaderboard(orderBy: 'distance')
        ]);
    }
}
