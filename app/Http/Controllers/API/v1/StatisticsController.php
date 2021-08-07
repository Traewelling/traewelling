<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Backend\StatisticController as StatisticBackend;
use App\Http\Resources\LeaderboardUserResource;
use App\Http\Resources\StatisticsTravelPurposeResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatisticsController extends ResponseController
{
    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboard(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard());
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboardByDistance(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard(orderBy: 'distance'));
    }

    /**
     * @return AnonymousResourceCollection
     * @todo Cannot be implemented unless login is working
     */
    public function leaderboardFriends(): AnonymousResourceCollection {
        return LeaderboardUserResource::collection(LeaderboardBackend::getLeaderboard(onlyFollowings: true));
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboardForMonth(string $date) {
        $date = Carbon::parse($date);
        return LeaderboardUserResource::collection(LeaderboardBackend::getMonthlyLeaderboard(date: $date));
    }

    public function getPersonalStatistics(Request $request) {
        $validated = $request->validate([
                                            'from' => ['nullable', 'date'],
                                            'to'   => ['nullable', 'date', 'after_or_equal:from']
                                        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subWeeks(4);
        $to   = isset($validated['to']) ? Carbon::parse($validated['to']) : Carbon::now();

        $travelPurposes = StatisticsTravelPurposeResource::collection(StatisticBackend::getTravelPurposes(auth()->user(), $from, $to));
        $topCategories  = StatisticBackend::getTopTravelCategoryByUser(auth()->user(), $from, $to);
        $topOperators   = StatisticBackend::getTopTripOperatorByUser(auth()->user(), $from, $to);
        $travelTime     = StatisticBackend::getWeeklyTravelTimeByUser(auth()->user(), $from, $to);

        $returnarray = [
            'purpose'    => $travelPurposes,
            'categories' => $topCategories,
            'operators'  => $topOperators,
            'time'       => $travelTime
        ];

        return $this->sendv1Response($returnarray);

    }
}
