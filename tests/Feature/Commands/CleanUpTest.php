<?php

namespace Tests\Feature\Commands;

use App\Models\Checkin;
use App\Models\Like;
use App\Models\PolyLine;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\StatusLiked;
use App\Services\PolylineStorageService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\Exception;
use Tests\FeatureTestCase;

class CleanUpTest extends FeatureTestCase
{

    use RefreshDatabase;

    public function testOldNotificationsGetRemoved(): void {
        //Check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        //Create a user, a liking user, a checkin, a like and a notification
        $user       = User::factory()->create();
        $likingUser = User::factory()->create();
        $checkin    = Checkin::factory(['user_id' => $user->id])->create();
        $like       = Like::factory([
                                        'user_id'   => $likingUser->id,
                                        'status_id' => $checkin->status_id,
                                    ])->create();
        $user->notify(new StatusLiked($like));
        $this->assertDatabaseCount('notifications', 1);

        //Notification should not be removed yet
        $this->artisan('app:clean-db:notifications')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('notifications', 1);

        //Mark notification as read
        $user->notifications->first()->markAsRead();

        //Notification should not be removed yet
        $this->artisan('app:clean-db:notifications')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('notifications', 1);

        //Simulate 31 days passing
        $this->travel(31)->days();

        //Notification should be removed now
        $this->artisan('app:clean-db:notifications')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function testUnusedTripsAreDeleted(): void {
        //create an unused trip
        Trip::factory()->create();
        $this->assertDatabaseCount('hafas_trips', 1);
        $this->artisan('app:clean-db:trips')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('hafas_trips', 0);

        //create a checkin (factory creates a trip)
        Checkin::factory()->create();
        $this->assertDatabaseCount('hafas_trips', 1);
        $this->artisan('app:clean-db:trips')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('hafas_trips', 1);
    }

    public function testOldPasswordResetRequestsAreDeleted(): void {
        $this->assertDatabaseCount('password_resets', 0);

        $user = User::factory()->create();
        Password::createToken($user);
        $this->assertDatabaseCount('password_resets', 1);

        $this->travel(2)->hours();

        $this->artisan('app:clean-db:password-resets')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('password_resets', 0);
    }

    public function testUsersThatHaventAcceptedPrivacyPolicyWithinADayAreRemoved(): void {
        $this->assertDatabaseCount('users', 0);

        User::factory(['privacy_ack_at' => null, 'created_at' => now()])->create();
        $this->assertDatabaseCount('users', 1);

        //should not be removed yet
        $this->artisan('app:clean-db:user')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('users', 1);

        //should be removed 25 hours later
        $this->travel(25)->hours();
        $this->artisan('app:clean-db:user')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * @throws Exception
     */
    public function testPolylineWithoutAnyReferenceAreDeleted(): void {
        $this->assertDatabaseCount('poly_lines', 0);
        $service = new PolylineStorageService();

        $polyline = PolyLine::create([
                                         'hash'     => Str::uuid(),
                                         'polyline' => json_encode(['some json data']),
                                     ]);
        $content  = $polyline->polyline; // this will store the polyline in the storage
        $hash     = $polyline->hash;
        $this->assertDatabaseCount('poly_lines', 1);
        $this->assertSame($content, $service->get($hash));

        $this->artisan('app:clean-db:polylines')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('poly_lines', 0);
        $this->assertSame('', $service->get($hash));

        //create a polyline with a reference and a parent
        //Checkin Factory creates a trip which creates a polyline
        $checkin = Checkin::factory()->create();
        $this->assertDatabaseCount('poly_lines', 1);

        //create a second polyline for testing parent deletion (this can be a Brouter polyline)
        $polyline = PolyLine::create([
                                         'hash'      => Str::uuid(),
                                         'polyline'  => json_encode(['some json data']),
                                         'parent_id' => $checkin->trip->polyline_id,
                                     ]);
        $checkin->trip->update(['polyline_id' => $polyline->id]);
        $this->assertDatabaseCount('poly_lines', 2);

        //no polylines should be deleted
        $this->artisan('app:clean-db:polylines')->assertExitCode(Command::SUCCESS);
        $this->assertDatabaseCount('poly_lines', 2);
    }

    public function testLeaderboardCachingCommand(): void {
        //there is no complex logic in the command, so we just check if it runs without errors
        $this->artisan('trwl:cache:leaderboard')->assertExitCode(Command::SUCCESS);
    }
}
