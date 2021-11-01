<?php

namespace App\Console;

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
        $schedule->command('trwl:cleanUpUsers')->dailyAt("1:30");
        $schedule->command('trwl:cleanUpHafasTrips')->dailyAt("1:35");
        $schedule->command('trwl:cleanUpPolylines')->dailyAt("1:40");
        $schedule->command('trwl:cleanUpPasswordResets')->dailyAt('1:45');
        $schedule->command('trwl:refreshTrips')->everyMinute();
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
