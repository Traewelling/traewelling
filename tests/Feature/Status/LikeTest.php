<?php

namespace Tests\Feature\Status;

use App\Http\Controllers\Backend\UserController;
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

        $response = $this->actingAs($likingUser)
                         ->post(route('like.create'), ['statusId' => $status->id]);
        $response->assertStatus(201);

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

        $response = $this->actingAs($likingUser)
                         ->post(route('like.create'), ['statusId' => $status->id]);
        $response->assertStatus(201);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0);
    }

    public function testRemovedLikesDontAppearInNotifications(): void {
        $trainCheckIn = TrainCheckin::factory()->create();
        $status       = $trainCheckIn->status;
        $likingUser   = User::factory(['privacy_ack_at' => Carbon::now()])->create();

        $response = $this->actingAs($likingUser)
                         ->post(route('like.create'), ['statusId' => $status->id]);
        $response->assertStatus(201);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1);

        $response = $this->actingAs($likingUser)
                         ->post(route('like.destroy'), ['statusId' => $status->id]);
        $response->assertStatus(200);

        $notifications = $this->actingAs($status->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0);
    }
}
