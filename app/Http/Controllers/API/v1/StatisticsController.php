<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaderBoardUserResource;
use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatisticsController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboard(): AnonymousResourceCollection {
        return LeaderBoardUserResource::collection(LeaderboardBackend::getLeaderboard());
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboardByDistance(): AnonymousResourceCollection {
        return LeaderBoardUserResource::collection(LeaderboardBackend::getLeaderboard(orderBy: 'distance'));
    }

    /**
     * @return AnonymousResourceCollection
     * @todo Cannot be implemented unless login is working
     */
    public function leaderboardFriends(): AnonymousResourceCollection {
        die();
        return LeaderBoardUserResource::collection(LeaderboardBackend::getLeaderboard(onlyFollowings: true));
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function leaderboardForMonth(string $date) {
        $date = Carbon::parse($date);
        return LeaderBoardUserResource::collection(LeaderboardBackend::getMonthlyLeaderboard(date: $date));
    }
}
