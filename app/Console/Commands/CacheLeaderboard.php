<?php

namespace App\Console\Commands;

use App\Helpers\CacheKey;
use App\Http\Controllers\Backend\LeaderboardController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CacheLeaderboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trwl:cache:leaderboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache of the Leaderboard so the requests';

    /**
     * Execute the console command.
     */
    public function handle(LeaderboardController $leaderboardController): int
    {
        DB::beginTransaction();
        Cache::forget(CacheKey::LEADERBOARD_GLOBAL_POINTS);
        Cache::forget(CacheKey::LEADERBOARD_GLOBAL_DISTANCE);
        $leaderboardController->getCachedGlobalLeaderboard();
        $leaderboardController->getCachedDistanceLeaderboard();
        $leaderboardController->getCachedFriendsLeaderboard();
        DB::commit();

        return Command::SUCCESS;
    }
}
