<?php

namespace App\Console;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function() {
            DB::table('users')->update(array('points' => '0'));
        })->weeklyOn(5, '03:14')
            ->runInBackground();

        //delete new users without GDPR Agreement
        $schedule->call(function() {
            $privacyUsers = User::where('privacy_ack_at', null)
                                ->where('created_at', '>', DB::raw('(NOW() - INTERVAL 1 DAY)'))
                                ->get();
            foreach ($privacyUsers as $user) {
                $user->delete();
            }
        })->daily()->runInBackground();

        $schedule->command('trwl:refreshTrips')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
