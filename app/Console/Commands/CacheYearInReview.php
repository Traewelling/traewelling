<?php

namespace App\Console\Commands;

use App\Http\Controllers\Backend\Stats\YearInReviewController;
use App\Models\Checkin;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class CacheYearInReview extends Command
{

    protected $signature   = 'trwl:cache-year-in-review {--year=}';
    protected $description = 'Cache the year in review for all users for a given year';

    public function handle(): int {
        $year = $this->option('year') ?? date('Y');
        $this->info('Caching year in review for year ' . $year . '...');
        $userIdsQ  = Checkin::whereBetween('departure', [Carbon::create($year), Carbon::create($year, 12, 31)])
                            ->select('user_id')
                            ->distinct();
        $users     = User::whereIn('id', $userIdsQ)->get();
        $count     = $users->count();
        $iteration = 0;
        $this->info('Found ' . $count . ' users.');
        foreach ($users as $user) {
            try {
                $this->info('[' . ++$iteration . '/' . $count . '] Caching year in review for user ' . $user->id . ' (' . $user->username . ")...");
                YearInReviewController::renew($user, $year);
            } catch (Exception $exception) {
                report($exception);
                $this->error('Failed to cache year in review for user ' . $user->id . ' (' . $user->username . ')');
            }
        }

        return Command::SUCCESS;
    }
}
