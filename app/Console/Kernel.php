<?php

namespace App\Console;

use App\Console\Commands\CacheLeaderboard;
use App\Console\Commands\CacheYearInReview;
use App\Console\Commands\CleanUpProfilePictures;
use App\Console\Commands\DatabaseCleaner\DatabaseCleaner;
use App\Console\Commands\DatabaseCleaner\MastodonServers;
use App\Console\Commands\HideStatus;
use App\Console\Commands\RefreshCurrentTrips;
use App\Console\Commands\WikidataFetcher;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void {
        //every minute
        $schedule->command(RefreshCurrentTrips::class)->withoutOverlapping()->everyMinute();

        //every five minutes
        $schedule->command(CacheLeaderboard::class)->withoutOverlapping()->everyFiveMinutes();

        //hourly tasks
        $schedule->command(HideStatus::class)->hourly();

        //daily tasks
        $schedule->command(DatabaseCleaner::class)->daily();
        $schedule->command(CleanUpProfilePictures::class)->daily();

        //weekly tasks
        $schedule->command(MastodonServers::class)->weekly();

        if (config('trwl.year_in_review.backend')) {
            $schedule->command(CacheYearInReview::class)->withoutOverlapping()->dailyAt('2:00');
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
