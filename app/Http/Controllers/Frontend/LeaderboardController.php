<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function renderMonthlyLeaderboard(string $month): View
    {
        $date = $this->parseMonth($month);

        return view('vue.spa', [
            'title' => __('menu.leaderboard'),
            'canonical' => route('leaderboard.monthly', ['month' => $date->format('Y-m')]),
            'robots' => 'index',
            'description' => __('description.leaderboard.monthly', [
                'month' => $date->isoFormat('MMMM'),
                'year' => $date->isoFormat('YYYY'),
            ]),
        ]);
    }

    public function redirectToMonthlyLeaderboard(string $date): RedirectResponse
    {
        return redirect()->route('leaderboard.monthly', ['month' => $this->parseMonth($date)->format('Y-m')], 301);
    }

    private function parseMonth(string $month): Carbon
    {
        try {
            return Carbon::parse($month);
        } catch (\Exception) {
            abort(400);
        }
    }
}
