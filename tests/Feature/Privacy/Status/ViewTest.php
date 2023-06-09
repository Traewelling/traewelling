<?php

namespace Tests\Feature\Privacy\Status;

use App\Enum\StatusVisibility;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class ViewTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUnauthenticatedViewPublicStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::PUBLIC->value])
                        ->has(TrainCheckin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('statuses.get', ['id' => $status->id]));
        $statusRequest->assertStatus(200);
    }

    public function testUnauthenticatedViewPrivateStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::PRIVATE])
                        ->has(TrainCheckin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('statuses.get', ['id' => $status->id]));
        $statusRequest->assertStatus(403);
    }

    public function testUnauthenticatedViewUnlistedStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::UNLISTED])
                        ->has(TrainCheckin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('statuses.get', ['id' => $status->id]));
        $statusRequest->assertStatus(200);
    }

    public function testUnauthenticatedViewFollowersOnlyStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::FOLLOWERS])
                        ->has(TrainCheckin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('statuses.get', ['id' => $status->id]));
        $statusRequest->assertStatus(403);
    }

    public function testUnauthenticatedViewOnlyAuthenticatedUsersStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::AUTHENTICATED])
                        ->has(TrainCheckin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('statuses.get', ['id' => $status->id]));
        $statusRequest->assertStatus(403);
    }

    public function testViewOwnStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testViewForeignPublicStatus(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::PUBLIC,
                                       ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testViewForeignFollowersOnlyStatusAndNotFollowing(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::FOLLOWERS,
                                       ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function testViewForeignFollowersOnlyStatusAndIsFollowing(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        Follow::factory([
                            'user_id'   => $user->id,
                            'follow_id' => $foreignUser->id,
                        ])->create();
        $status = Status::factory([
                                      'user_id'    => $foreignUser->id,
                                      'visibility' => StatusVisibility::FOLLOWERS,
                                  ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testViewForeignPrivateStatus(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::PRIVATE,
                                       ])->create();
        $this->assertFalse($user->can('view', $status));
    }

    public function testViewForeignOnlyAuthenticatedUsersStatus(): void {
        $user        = User::factory()->create();
        $foreignUser = User::factory()->create();
        $status      = Status::factory([
                                           'user_id'    => $foreignUser->id,
                                           'visibility' => StatusVisibility::AUTHENTICATED
                                       ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testPublicStatusFromPrivateProfileIsNotDisplayedOnEventsPage(): void {
        //create test scenario: Public Status with Event and Private Profile
        $event  = Event::factory()->create();
        $trainCheckin = TrainCheckin::factory()->create();
        $trainCheckin->status->update([
                                          'visibility' => StatusVisibility::PUBLIC,
                                          'event_id'   => $event->id,
                                      ]);
        $trainCheckin->user->update(['private_profile' => true]);

        //request statuses for event
        $response = $this->get("/api/v1/event/{$event->slug}/statuses");
        $response->assertOk();

        //check if status is not displayed
        $response->assertJsonCount(0, 'data');
    }
}
