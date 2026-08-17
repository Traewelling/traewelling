<?php

namespace App\Http\Controllers\Frontend;

use App\Dto\Internal\GlobalCheckinStats;
use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\StatisticController;
use App\Http\Controllers\Backend\VersionController;
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

    private function getVisibilityLevels(): array
    {
        return array_map(static fn (StatusVisibility $visibility): array => [
            'value' => $visibility->value,
            'label' => __('status.visibility.' . $visibility->value),
            'detail' => __('status.visibility.' . $visibility->value . '.detail'),
        ], StatusVisibility::cases());
    }

    private function getShowcaseTags(): array
    {
        $tags = [
            [StatusTagKey::TICKET, 'Deutschlandticket', StatusVisibility::PUBLIC],
            [StatusTagKey::WAGON, '12', StatusVisibility::PUBLIC],
            [StatusTagKey::SEAT, '42', StatusVisibility::FOLLOWERS],
            [StatusTagKey::TRAVEL_CLASS, '2', StatusVisibility::PUBLIC],
            [StatusTagKey::VEHICLE_NUMBER, 'Tz 9481', StatusVisibility::PUBLIC],
            [StatusTagKey::PRICE, '0,00 €', StatusVisibility::PRIVATE],
        ];

        return array_map(static fn (array $tag): array => [
            'label' => __('tag.title.' . $tag[0]->value),
            'value' => $tag[1],
            'visibility' => $tag[2]->value,
        ], $tags);
    }

    public function renderLandingPage(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('welcome/welcome', [
            'stats' => $this->getStats(),
            'version' => VersionController::getVersion(),
            'visibilityLevels' => $this->getVisibilityLevels(),
            'showcaseTags' => $this->getShowcaseTags(),
        ]);
    }
}
