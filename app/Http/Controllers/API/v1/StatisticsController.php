<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StatisticsController extends Controller
{
    public function leaderboard(): AnonymousResourceCollection {
        return UserResource::collection(LeaderboardBackend::getLeaderboard());
    }

    public function leaderboardByDistance(): AnonymousResourceCollection {
        return UserResource::collection(LeaderboardBackend::getLeaderboard(orderBy: 'distance'));
    }

    public function leaderboardFriends(): AnonymousResourceCollection {
        die();
        return UserResource::collection(LeaderboardBackend::getLeaderboard(onlyFollowings: true));
    }
}
