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
                                            'from'  => ['nullable', 'date'],
                                            'until' => ['nullable', 'date', 'after_or_equal:from']
                                        ]);

        $from  = isset($validated['from']) ? Carbon::parse($validated['from']) : Carbon::now()->subWeeks(4);
        $until = isset($validated['until']) ? Carbon::parse($validated['until']) : Carbon::now();

        $purposes   = StatisticsTravelPurposeResource::collection(
            StatisticBackend::getTravelPurposes(user: auth()->user(), from: $from, until: $until)
        );
        $categories = StatisticBackend::getTopTravelCategoryByUser(user: auth()->user(), from: $from, until: $until);
        $operators  = StatisticBackend::getTopTripOperatorByUser(user: auth()->user(), from: $from, until: $until);
        $travelTime = StatisticBackend::getWeeklyTravelTimeByUser(user: auth()->user(), from: $from, until: $until);

        $returnData = [
            'purpose'    => $purposes,
            'categories' => $categories,
            'operators'  => $operators,
            'time'       => $travelTime
        ];

        $additionalData = [
            'meta' => [
                'from'  => $from,
                'until' => $until
            ]
        ];

        return $this->sendv1Response(data: $returnData, code: 200, additional: $additionalData);

    }
}
