<?php

namespace Tests\Feature\Commands;

use App\Models\Like;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Notifications\StatusLiked;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanUpTest extends TestCase
{

    use RefreshDatabase;

    public function testOldNotificationsGetRemoved(): void {
        //Check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        //Create a user, a liking user, a checkin, a like and a notification
        $user       = User::factory()->create();
        $likingUser = User::factory()->create();
        $checkin    = TrainCheckin::factory(['user_id' => $user->id])->create();
        $like       = Like::factory([
                                        'user_id'   => $likingUser->id,
                                        'status_id' => $checkin->status_id,
                                    ])->create();
        $user->notify(new StatusLiked($like));
        $this->assertDatabaseCount('notifications', 1);

        //Notification should not be removed yet
        $this->artisan('trwl:cleanUpNotifications')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('notifications', 1);

        //Mark notification as read
        $user->notifications->first()->markAsRead();

        //Notification should not be removed yet
        $this->artisan('trwl:cleanUpNotifications')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('notifications', 1);

        //Simulate 31 days passing
        $this->travel(31)->days();

        //Notification should be removed now
        $this->artisan('trwl:cleanUpNotifications')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('notifications', 0);
    }
}
