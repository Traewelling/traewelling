<?php

namespace App\Console\Commands\DatabaseCleaner;

use App\Models\YearInReviewCache;
use Carbon\Carbon;
use Illuminate\Console\Command;

class YearInReview extends Command
{
    protected $signature = 'app:clean-db:year-in-review {--days=30}';

    protected $description = 'Delete old year in review cache entries';

    public function handle(): int
    {
        $days = $this->option('days');
        $deleted = YearInReviewCache::where('updated_at', '<', Carbon::now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} old yir-cache entries.");

        return Command::SUCCESS;
    }
}
