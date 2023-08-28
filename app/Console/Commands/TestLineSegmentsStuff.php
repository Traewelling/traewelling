<?php

namespace App\Console\Commands;

use App\Enum\CacheKey;
use App\Http\Controllers\Frontend\LeaderboardController;
use App\Http\Controllers\Locations\LineRunController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestLineSegmentsStuff extends Command
{
    protected $signature = 'trwl:line {flag?}';


    public function handle(): int {
        $flag      = $this->arguments()['flag'];
        $lineRunController = new LineRunController();
        if ($flag) {
            $lineRunController->showDemo();
        } else {
            $lineRunController->demo();
        }

        return Command::SUCCESS;
    }
}
