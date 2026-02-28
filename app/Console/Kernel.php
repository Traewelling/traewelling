<?php

namespace App\Console;

use App\Console\Commands\CacheLeaderboard;
use App\Console\Commands\CacheYearInReview;
use App\Console\Commands\CleanUpProfilePictures;
use App\Console\Commands\DatabaseCleaner\DatabaseCleaner;
use App\Console\Commands\DatabaseCleaner\MastodonServers;
use App\Console\Commands\FetchTransitousLicenses;
use App\Console\Commands\HideStatus;
use App\Console\Commands\ReduceRelevance;
use App\Console\Commands\RefreshCurrentTrips;
use App\Console\Commands\RefreshOperatorMappings;
use App\Jobs\FetchManualTransitousLicenses;
use App\Jobs\MigrationStationIdentifiers;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\PersonalDataExport\Commands\CleanOldPersonalDataExportsCommand;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // every minute
        $schedule->command(RefreshCurrentTrips::class)->withoutOverlapping()->everyMinute();

        // every five minutes
        $schedule->command(CacheLeaderboard::class)->withoutOverlapping()->everyFiveMinutes();
        $schedule->job(MigrationStationIdentifiers::class)->withoutOverlapping()->everyFiveMinutes();
        $schedule->job(MigrationStationIdentifiers::class)->withoutOverlapping()->between('00:00', '02:00')->everyThirtySeconds();

        // hourly tasks
        $schedule->command(HideStatus::class)->hourly();
        $schedule->command(RefreshOperatorMappings::class)->hourly();

        // every six hours
        $schedule->job(FetchManualTransitousLicenses::class)->everySixHours();

        // daily tasks
        $schedule->command(DatabaseCleaner::class)->daily();
        $schedule->command(CleanUpProfilePictures::class)->daily();
        $schedule->command(CleanOldPersonalDataExportsCommand::class)->daily();
        $schedule->command(FetchTransitousLicenses::class)->daily();
        $schedule->command(ReduceRelevance::class)->daily();

        // weekly tasks
        $schedule->command(MastodonServers::class)->weekly();

        if (config('trwl.year_in_review.scheduler')) {
            $schedule->command(CacheYearInReview::class)->withoutOverlapping()->dailyAt('2:00');
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
