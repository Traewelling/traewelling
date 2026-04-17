<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;

class LeaderboardController extends Controller
{
    public function __construct(
        private readonly LeaderboardBackend $leaderboardBackend,
    ) {}

    public function renderBetaMonthlyLeaderboard(string $month): Renderable|RedirectResponse
    {
        if (auth()->user()?->hasRole('open-beta')) {
            return view('vue.spa');
        }

        return redirect()->route('leaderboard.month', ['date' => $month]);
    }

    public function renderMonthlyLeaderboard(string $date): Renderable|RedirectResponse
    {
        if (auth()->user()?->points_enabled === false) {
            return redirect()->route('dashboard');
        }

        try {
            $date = Carbon::parse($date);
        } catch (\Exception) {
            abort(400);
        }

        return view('leaderboard.month', [
            'leaderboard' => $this->leaderboardBackend->getCachedMonthlyLeaderboard($date),
            'date' => Carbon::parse($date),
        ]);
    }
}
