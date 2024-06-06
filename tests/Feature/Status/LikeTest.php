<?php

namespace Tests\Feature\Status;

use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Models\Checkin;
use App\Models\User;
use App\Notifications\StatusLiked;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FeatureTestCase;

class LikeTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function testLikesAppearsInNotifications(): void {
        $checkin    = Checkin::factory()->create();
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
        $checkin    = Checkin::factory()->create();
        $likingUser = User::factory()->create();

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        UserController::muteUser($checkin->status->user, $likingUser);
        StatusBackend::createLike($likingUser, $checkin->status);

        //check that there are still no notifications
        $this->assertDatabaseCount('notifications', 0);
    }

    public function testLikingDoesNotWorkIfIHaveDisabledLikes(): void {
        $checkin    = Checkin::factory()->create();
        $likingUser = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $checkin->status->user->update(['likes_enabled' => false]);

        $this->expectException(AuthorizationException::class);
        StatusBackend::createLike($likingUser, $checkin->status);
    }

    public function testOldLikesStillAppearInNotificationsIfIHaveDisabledLikes(): void {
        //create checkin and a liking user
        $checkin    = Checkin::factory()->create();
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
        $checkin    = Checkin::factory()->create();
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
        $checkin    = Checkin::factory()->create();
        $likingUser = User::factory()->create();

        $checkin->status->user->update(['likes_enabled' => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $checkin->status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }

    public function testLikeButtonDoesNotAppearForGuestIfAuthorHasDisabledLike(): void {
        $checkin    = Checkin::factory()->create();
        $likingUser = User::factory()->create();

        $checkin->status->user->update(["likes_enabled" => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $checkin->status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }

    public function testLikeButtonDoesNotAppearIfIHaveDisabledLike(): void {
        $checkin    = Checkin::factory()->create();
        $likingUser = User::factory()->create();

        $likingUser->update(['likes_enabled' => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $checkin->status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }
}
