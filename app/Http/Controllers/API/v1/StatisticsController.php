<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Backend\LeaderboardController as LeaderboardBackend;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function leaderboard() {
        return UserResource::collection(LeaderboardBackend::getLeaderboard());
    }
}
