<?php

namespace Tests\Feature\Privacy\Status;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class ViewTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUnauthenticatedViewPublicStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::PUBLIC->value])
                        ->has(Checkin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('status', ['id' => $status->id]));
        $statusRequest->assertStatus(200);
    }

    public function testUnauthenticatedViewPrivateStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::PRIVATE])
                        ->has(Checkin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('status', ['id' => $status->id]));
        $statusRequest->assertStatus(403);
    }

    public function testUnauthenticatedViewUnlistedStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::UNLISTED])
                        ->has(Checkin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('status', ['id' => $status->id]));
        $statusRequest->assertStatus(200);
    }

    public function testUnauthenticatedViewFollowersOnlyStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::FOLLOWERS])
                        ->has(Checkin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('status', ['id' => $status->id]));
        $statusRequest->assertStatus(403);
    }

    public function testUnauthenticatedViewOnlyAuthenticatedUsersStatus(): void {
        $user   = User::factory()->create();
        $status = Status::factory(['user_id' => $user->id, 'visibility' => StatusVisibility::AUTHENTICATED])
                        ->has(Checkin::factory())
                        ->create();

        $this->assertGuest();
        $statusRequest = $this->get(route('status', ['id' => $status->id]));
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
                                           'visibility' => StatusVisibility::AUTHENTICATED,
                                       ])->create();
        $this->assertTrue($user->can('view', $status));
    }

    public function testPublicStatusFromPrivateProfileIsNotDisplayedOnEventsPage(): void {
        //create test scenario: Public Status with Event and Private Profile
        $event   = Event::factory()->create();
        $checkin = Checkin::factory()->create();
        $checkin->status->update([
                                     'visibility' => StatusVisibility::PUBLIC,
                                     'event_id'   => $event->id,
                                 ]);
        $checkin->user->update(['private_profile' => true]);

        //request statuses for event
        $response = $this->get("/api/v1/event/{$event->slug}/statuses");
        $response->assertOk();

        //check if status is not displayed
        $response->assertJsonCount(0, 'data');
    }

    public function testUnlistedStatusPolicyIsWorkingCorrectly(): void {
        //create alice and bob
        $alice = User::factory()->create();
        $bob   = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        //create an unlisted status for bob
        $checkin = Checkin::factory(['user_id' => $bob->id])->create();
        $checkin->status->update(['visibility' => StatusVisibility::UNLISTED]);

        //alice should not see the status on her global dashboard
        $response = $this->get("/api/v1/dashboard/global");
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        //alice follows bob
        FollowBackend::createOrRequestFollow($alice, $bob);

        //alice should not see the status on her (followers) dashboard
        $response = $this->get("/api/v1/dashboard");
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        //alice should not see the status on active journeys
        $response = $this->get('/api/v1/statuses');
        $response->assertOk();
        $response->assertJsonCount(0, 'data');

        //alice should see the status on bobs profile
        $response = $this->get("/api/v1/user/{$bob->username}/statuses");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        //alice should see the status if queried directly
        $response = $this->get("/api/v1/status/{$checkin->status->id}");
        $response->assertOk();
        $response->assertJsonCount(1);
    }

    public function testAuthenticatedStatusIsNotVisibleForLoggedOutUsersOnEventPage(): void {
        //create test scenario: Authenticated Status with Event
        $event   = Event::factory()->create();
        $checkin = Checkin::factory()->create();
        $checkin->status->update([
                                     'visibility' => StatusVisibility::AUTHENTICATED,
                                     'event_id'   => $event->id,
                                 ]);

        $this->assertGuest();

        $response = $this->get('/event/' . $event->slug);
        $response->assertOk();

        //check if status is not displayed
        $response->assertDontSee('/status/' . $checkin->status->id);
    }
}
