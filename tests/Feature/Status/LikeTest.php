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
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        StatusBackend::createLike($likingUser, $status);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1);
        $notifications->assertJsonFragment([
                                               'type'            => StatusLiked::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $status->user->id
                                           ]);
    }

    public function testLikesFromMutedUsersDontAppearInNotifications(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        UserController::muteUser($status->user, $likingUser);
        StatusBackend::createLike($likingUser, $status);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0);
    }

    public function testLikingDoesNotWorkIfIHaveDisabledLikes(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $status->user->update(["likes_enabled" => false]);

        $this->expectException(PermissionException::class);
        StatusBackend::createLike($likingUser, $status);
    }

    public function testOldLikesStillAppearInNotificationsIfIHaveDisabledLikes(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();
        StatusBackend::createLike($likingUser, $status);

        $status->user->update(["likes_enabled" => false]);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1);
    }

    public function testRemovedLikesDontAppearInNotifications(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        StatusBackend::createLike($likingUser, $status);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1);

        StatusBackend::destroyLike($likingUser, $status->id);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0);
    }

    public function testLikeButtonDoesNotAppearForLoggedInUserIfAuthorHasDisabledLike(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $status->user->update(["likes_enabled" => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }

    public function testLikeButtonDoesNotAppearForGuestIfAuthorHasDisabledLike(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $status->user->update(["likes_enabled" => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }

    public function testLikeButtonDoesNotAppearIfIHaveDisabledLike(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $likingUser->update(["likes_enabled" => false]);

        $notifications = $this->actingAs($likingUser)
                              ->get("/status/" . $status->id);
        $notifications->assertOk();
        $notifications->assertDontSee("class=\"like ");
    }
}
