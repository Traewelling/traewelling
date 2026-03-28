<?php

namespace Tests\Feature\APIv1;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\User\FollowController as FollowBackend;
use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\Checkin;
use App\Models\Event;
use App\Models\EventSuggestion;
use App\Models\Follow;
use App\Models\User;
use App\Notifications\EventSuggestionProcessed;
use App\Notifications\UserFollowed;
use App\Notifications\UserJoinedConnection;
use App\Services\Checkin\CheckinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;
use Tests\Helpers\CheckinRequestTestHydrator;

class NotificationsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_unread_count_endpoint(): void
    {
        // create users
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // check if there are no notifications in the database
        $this->assertDatabaseCount('notifications', 0);

        // bob follows alice - this should spawn a notification
        FollowBackend::createOrRequestFollow($bob, $alice);

        // check if there is one notification in the database
        $this->assertDatabaseCount('notifications', 1);

        // check if api returns one unread notification
        $response = $this->get(uri: '/api/v1/notifications/unread/count');
        $response->assertOk();
        $response->assertJsonFragment(['data' => 1]);
    }

    public function test_api_endpoint_can_mark_notification_as_read_and_unread(): void
    {
        // create users
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        // check if there are no notifications in the database
        $this->assertDatabaseCount('notifications', 0);

        // bob follows alice - this should spawn a notification
        FollowBackend::createOrRequestFollow($bob, $alice);

        // check if notification is unread
        $notification = $alice->notifications()->first();
        $this->assertNull($notification->read_at);

        // try to mark non-existing notification as read -> should fail
        $response = $this->put('/api/v1/notifications/read/non-existing-id');
        $response->assertNotFound();

        // mark notification as read
        $response = $this->put("/api/v1/notifications/read/{$notification->id}");
        $response->assertOk();

        // check if notification is read
        $notification = $alice->notifications()->first();
        $this->assertNotNull($notification->read_at);

        // try to mark non-existing notification as unread -> should fail
        $response = $this->put('/api/v1/notifications/unread/non-existing-id');
        $response->assertNotFound();

        // mark notification as unread
        $response = $this->put("/api/v1/notifications/unread/{$notification->id}");
        $response->assertOk();

        // check if notification is unread
        $notification = $alice->notifications()->first();
        $this->assertNull($notification->read_at);
    }

    public function test_api_endpoint_can_mark_all_notification_as_read(): void
    {
        // create users
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        Passport::actingAs($alice, ['*']);

        // check if there are no notifications in the database
        $this->assertDatabaseCount('notifications', 0);

        // create some notifications
        for ($i = 0; $i < 10; $i++) {
            $status = Checkin::factory(['user_id' => $alice->id])->create()->status;
            StatusBackend::createLike($bob, $status);
        }

        // check if there are 10 notifications in the database
        $this->assertDatabaseCount('notifications', 10);
        $this->assertDatabaseHas('notifications', ['read_at' => null]);

        // mark all notifications as read
        $response = $this->put(uri: '/api/v1/notifications/read/all');
        $response->assertOk();

        // check if all notifications are read
        $this->assertDatabaseMissing('notifications', ['read_at' => null]);
    }

    public function test_following_a_user_should_spawn_a_notification(): void
    {
        // Create users
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($bob, ['*']);

        // Check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        // alice follows bob
        FollowBackend::createOrRequestFollow($alice, $bob);
        $follow = Follow::where('user_id', $alice->id)->where('follow_id', $bob->id)->first();

        // Check if there is one notification
        $this->assertDatabaseCount('notifications', 1);

        // bob should have one notification
        $response = $this->get('/api/v1/notifications');
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
        $response->assertJsonFragment([
            'type' => str_replace('App\\Notifications\\', '', UserFollowed::class),
        ]);
        $response->assertJsonFragment([
            'data' => [
                'follow' => [
                    'id' => $follow->id,
                ],
                'follower' => [
                    'id' => $alice->id,
                    'username' => $alice->username,
                    'name' => $alice->name,
                ],
            ],
        ]
        );
    }

    public function test_unfollowing_bob_should_remove_the_notification(): void
    {
        // create alice and bob
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        // check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        // alice follows bob
        FollowBackend::createOrRequestFollow($alice, $bob);

        // check if there is one notification
        $this->assertDatabaseCount('notifications', 1);

        // alice unfollows bob
        // TODO: improve backend function to accept two user models
        $follow = Follow::where('user_id', $alice->id)
            ->where('follow_id', $bob->id)
            ->firstOrFail();
        FollowBackend::removeFollower(follow: $follow, user: $bob);

        // check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_bob_joining_on_alices_connection_should_spawn_a_notification(): void
    {
        // create users
        $alice = User::factory()->create();
        $aliceCheckIn = Checkin::factory(['user_id' => $alice->id])->create();
        $bob = User::factory()->create();

        // Check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        // bob also checks into the train (with same origin and destination - but not relevant)
        $bobsData = new CheckinService()->checkin((new CheckinRequestTestHydrator($bob))->hydrateFromCheckin($aliceCheckIn));
        $bobStatus = $bobsData->status;

        // Check if there is one notification
        $this->assertDatabaseCount('notifications', 1);

        // Alice should have one notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $alice->id,
            'type' => UserJoinedConnection::class,
        ]);

        // bob deletes his status
        StatusBackend::DeleteStatus($bob, $bobStatus->id);

        // alice should have no notifications
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $alice->id,
            'type' => UserJoinedConnection::class,
        ]);
    }

    public function test_bob_joining_on_alices_connection_should_not_spawn_a_notification_when_private(): void
    {
        // GIVEN: A mocked checkin for Alice
        $alice = User::factory(['privacy_ack_at' => Carbon::now()])->create();
        $aliceCheckIn = Checkin::factory(['user_id' => $alice->id])->create();

        // Check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        // WHEN: Bob also checks into the train (with same origin and destination - but not relevant)
        $bob = User::factory(['privacy_ack_at' => Carbon::now()])->create();
        $dto = (new CheckinRequestTestHydrator($bob))->hydrateFromCheckin($aliceCheckIn);
        $dto->setStatusVisibility(StatusVisibility::PRIVATE);
        new CheckinService()->checkin($dto);

        // Check if there are no notifications
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_mark_notification_as_read(): void
    {
        // create alice and bob
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        Passport::actingAs($bob, ['*']);

        // alice follows bob
        UserBackend::createFollow($alice, $bob);

        // get should have a notification witch is not read
        $notification = $bob->notifications()->first();
        $this->assertNull($notification->read_at);

        // mark notification as read via api
        $response = $this->put("/api/v1/notifications/read/{$notification->id}");
        $response->assertOk();

        // check if notification is marked as read
        $notification = $bob->notifications()->first();
        $this->assertNotNull($notification->read_at);
    }

    public function test_deleting_a_user_should_delete_its_notifications(): void
    {
        // Given: Users Alice and Bob
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        // create a notification for bob by alice following bob
        FollowBackend::createOrRequestFollow($alice, $bob);

        // check if Bob has one notification
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $bob->id]);

        // bob deletes their account
        BackendUserController::deleteUserAccount($bob);

        // there should be no notifications left for bob
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $bob->id]);
    }

    public function test_accepting_event_suggestion_spawn_a_notification(): void
    {
        // Create users: alice suggests, bob accepts
        $alice = User::factory()->create();
        $bob = User::factory()->create()->assignRole('admin');

        // suggest an event
        $eventSuggestion = EventSuggestion::factory(['user_id' => $alice->id])->create();

        // accept event suggestion via API as bob
        Passport::actingAs($bob, ['*']);
        $this->postJson(
            "/api/v1/admin/event-suggestions/{$eventSuggestion->id}/accept",
            [
                'name' => $eventSuggestion->name,
                'checkin_start' => $eventSuggestion->begin->toDateString(),
                'checkin_end' => $eventSuggestion->end->toDateString(),
            ]
        )->assertCreated();

        // save event for later
        $event = Event::first();

        // let alice request her notifications
        Passport::actingAs($alice, ['*']);
        $response = $this->get('/api/v1/notifications');
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
        $response->assertJsonFragment([
            'type' => str_replace('App\\Notifications\\', '', EventSuggestionProcessed::class),
        ]);
        $response->assertJsonFragment([
            'data' => [
                'accepted' => true,
                'event' => [
                    'id' => $event->id,
                    'slug' => $event->slug,
                    'name' => $event->name,
                    'checkin_start' => $event->checkin_start,
                    'checkin_end' => $event->checkin_end,
                ],
                'suggestedName' => $eventSuggestion->name,
                'rejectionReason' => null,
            ],
        ]
        );
    }

    public function test_denying_event_suggestion_spawn_a_notification(): void
    {
        // Create users: alice suggests, bob denies
        $alice = User::factory()->create();
        $bob = User::factory()->create()->assignRole('admin');

        // suggest an event
        $eventSuggestion = EventSuggestion::factory(['user_id' => $alice->id])->create();

        // deny event suggestion via API as bob
        Passport::actingAs($bob, ['*']);
        $this->postJson(
            "/api/v1/admin/event-suggestions/{$eventSuggestion->id}/deny",
            ['reason' => 'denied']
        )->assertNoContent();

        // let alice request her notifications
        Passport::actingAs($alice, ['*']);
        $response = $this->actingAs($alice)
            ->get('/api/v1/notifications');
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
        $response->assertJsonFragment([
            'type' => str_replace('App\\Notifications\\', '', EventSuggestionProcessed::class),
        ]);
        $response->assertJsonFragment([
            'data' => [
                'accepted' => false,
                'event' => null,
                'suggestedName' => $eventSuggestion->name,
                'rejectionReason' => 'denied',
            ],
        ]
        );
    }
}
