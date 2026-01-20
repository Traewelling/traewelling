<?php

namespace App\Http\Controllers\Frontend;

use App\Dto\Internal\GlobalCheckinStats;
use App\Http\Controllers\Backend\StatisticController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class LandingPageController
{
    private const string CACHE_KEY_STATS = 'welcome.stats';

    private const string CACHE_KEY_STATS_TTL = 'welcome.stats.revalidate';

    private const string CACHE_KEY_STATS_CALCULATING = 'welcome.stats.calculating';

    private function getStats(): GlobalCheckinStats
    {
        $stats = Cache::get(self::CACHE_KEY_STATS);
        $ttl = Cache::get(self::CACHE_KEY_STATS_TTL, 0);
        $calculating = Cache::get(self::CACHE_KEY_STATS_CALCULATING, false);

        // refresh stats if they are outdated. rand(0,10) to reduce risk of multiple processes starting calculation
        // $calculating to make sure, that there won't be multiple processes calculating the stats in the next 15 Minutes
        if (
            ($stats === null
             || $ttl < now()->format('u'))
            && !$calculating
        ) {
            dispatch(function () {
                Cache::put(self::CACHE_KEY_STATS_CALCULATING, true, now()->addMinutes(15));
                $stats = StatisticController::getGlobalCheckInStatsAllTime();

                Cache::put(self::CACHE_KEY_STATS, $stats, now()->addDays(6));
                Cache::put(self::CACHE_KEY_STATS_TTL, now()->addDays(5)->format('u'));
            })->afterResponse();
        }

        // Fallback: Show bogus values if really nothing is set
        return $stats ?? new GlobalCheckinStats(0, 0, 0);
    }

    public function renderLandingPage(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('welcome/welcome', ['stats' => $this->getStats()]);
    }
}
