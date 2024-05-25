<?php

namespace App\Console;

use App\Console\Commands\DatabaseCleaner\DatabaseCleaner;
use App\Console\Commands\DatabaseCleaner\MastodonServers;
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
        $schedule->command(DatabaseCleaner::class)->dailyAt('1:30');

        $schedule->command('trwl:refreshTrips')->withoutOverlapping()->everyMinute();
        $schedule->command('trwl:hideStatus')->daily();
        $schedule->command('trwl:cache:leaderboard')->withoutOverlapping()->everyFiveMinutes();

        $schedule->command(MastodonServers::class)->weekly();
        $schedule->command('app:wikidata-fetcher')->everyMinute();

        if (config('trwl.year_in_review.backend')) {
            $schedule->command('trwl:cache-year-in-review')->withoutOverlapping()->dailyAt('2:00');
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
