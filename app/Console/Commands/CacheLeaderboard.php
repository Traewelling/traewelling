<?php

namespace App\Console\Commands;

use App\Helpers\CacheKey;
use App\Http\Controllers\Frontend\LeaderboardController;
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
     *
     * @return int
     */
    public function handle(LeaderboardController $leaderboardController): int {
        DB::beginTransaction();
        Cache::forget(CacheKey::LeaderboardGlobalPoints);
        Cache::forget(CacheKey::LeaderboardGlobalDistance);
        $leaderboardController->renderLeaderboard();
        DB::commit();

        return Command::SUCCESS;
    }
}
