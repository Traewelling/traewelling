<?php

namespace App\Console;

use App\Models\SocialLoginProfile;
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
            $privacyUsers = User::where('privacy_ack_at', null)->get();
            foreach($privacyUsers as $user) {
                if ($user->created_at < date('Y-m-d H:i:s', strtotime('-1 day'))) {
                    SocialLoginProfile::where('user_id', $user->id)->delete();
                    $user->delete();
                }
            }
        })->daily()->runInBackground();
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
