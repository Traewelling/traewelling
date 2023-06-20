<?php

namespace Tests\Feature\Status;

use App\Exceptions\PermissionException;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function testLikesAppearsInNotifications(): void {
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        StatusBackend::createLike($likingUser, $checkin->status);

        //check that there is a notification
        $this->assertDatabaseCount('notifications', 1);

        //check that the notification is of the correct type
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $checkin->status->user->id,
            'type'          => StatusLiked::class,
        ]);
    }

    public function testLikesFromMutedUsersDontAppearInNotifications(): void {
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        UserController::muteUser($checkin->status->user, $likingUser);
        StatusBackend::createLike($likingUser, $checkin->status);

        //check that there are still no notifications
        $this->assertDatabaseCount('notifications', 0);
    }

    public function testLikingDoesNotWorkIfIHaveDisabledLikes(): void {
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $checkin->status->user->update(['likes_enabled' => false]);

        $this->expectException(PermissionException::class);
        StatusBackend::createLike($likingUser, $checkin->status);
    }

    public function testOldLikesStillAppearInNotificationsIfIHaveDisabledLikes(): void {
        //create checkin and a liking user
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        //like the checkin - this should create a notification
        StatusBackend::createLike($likingUser, $checkin->status);

        //now there should be a notification
        $this->assertDatabaseCount('notifications', 1);

        //then disable likes
        $checkin->status->user->update(['likes_enabled' => false]);

        //the notification should still be there
        $this->assertDatabaseCount('notifications', 1);
    }

    public function testRemovedLikesDontAppearInNotifications(): void {
        //create checkin and a liking user
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        //like the checkin - this should create a notification
        StatusBackend::createLike($likingUser, $checkin->status);

        //now there should be a notification
        $this->assertDatabaseCount('notifications', 1);

        //then remove the like - this should remove the notification
        StatusBackend::destroyLike($likingUser, $checkin->status->id);

        //check that there are no notifications left
        $this->assertDatabaseCount('notifications', 0);
    }

    public function testLikeButtonDoesNotAppearForLoggedInUserIfAuthorHasDisabledLike(): void {
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        $checkin->status->user->update(['likes_enabled' => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $checkin->status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }

    public function testLikeButtonDoesNotAppearForGuestIfAuthorHasDisabledLike(): void {
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        $checkin->status->user->update(["likes_enabled" => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $checkin->status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }

    public function testLikeButtonDoesNotAppearIfIHaveDisabledLike(): void {
        $checkin    = TrainCheckin::factory()->create();
        $likingUser = User::factory()->create();

        $likingUser->update(['likes_enabled' => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $checkin->status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }
}
