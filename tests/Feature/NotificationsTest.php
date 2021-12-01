<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\Like;
use App\Models\User;
use App\Notifications\StatusLiked;
use App\Notifications\UserFollowed;
use App\Notifications\UserJoinedConnection;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void {
        parent::setUp();

        $this->user = $this->createGDPRAckedUser();
    }

    /** @test */
    public function likes_appear_in_notifications(): void {
        // Given: There is a likable status
        $timestamp = Carbon::parse("+2 day 7:45");
        $this->checkin("Hamburg Hbf", $timestamp);

        $status = $this->user->statuses->first();

        // When: Someone (e.g. the user itself) likes the status
        $like = $this->actingAs($this->user)
                     ->post(route('like.create'), ['statusId' => $status->id]);
        $like->assertStatus(201); // Created

        // Then: The like appears in the notifications
        $notifications = $this->actingAs($this->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one like
        $notifications->assertJsonFragment([
                                               'type'            => StatusLiked::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $this->user->id
                                           ]);
    }

    /** @test */
    public function removed_likes_dont_appear_in_notifications(): void {
        // Given: There is a likable status
        $timestamp = Carbon::parse("+2 day 7:45");
        $this->checkin("Hamburg Hbf", $timestamp);

        $status = $this->user->statuses->first();
        $like   = $this->actingAs($this->user)
                       ->post(route('like.create'), ['statusId' => $status->id]);
        $like->assertStatus(201); // Created

        // When: The like is removed
        Like::first()->delete();

        // Then: It does not show up in the notifications anymore
        $notifications = $this->actingAs($this->user)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0); // no likes left
    }

    /** @test */
    public function following_a_user_should_spawn_a_notification(): void {
        // Given: Users Alice and Bob
        $alice = $this->user;
        $bob   = $this->createGDPRAckedUser();

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // Then: Bob should see that in their notifications
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one follow
        $notifications->assertJsonFragment([
                                               'type'            => UserFollowed::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $bob->id
                                           ]);
    }

    /** @test */
    public function unfollowing_bob_should_remove_the_notification(): void {
        // Given: Users Alice and Bob and Alice follows Bob
        $alice  = $this->user;
        $bob    = $this->createGDPRAckedUser();
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // When: Alice unfollows Bob
        $unfollow = $this->actingAs($alice)->post(route('follow.destroy'), ['follow_id' => $bob->id]);
        $unfollow->assertStatus(200);

        // Then: Bob should not see that notification anymore
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0); // no follow no more
    }

    /** @test */
    public function bob_joining_on_alices_connection_should_spawn_a_notification(): void {
        // GIVEN: Alice checked-into a train.
        $alice     = $this->createGDPRAckedUser();
        $timestamp = Carbon::parse("+2 day 7:45");
        $this->checkin("Hamburg Hbf", $timestamp, $alice);

        // WHEN: Bob also checks into the train
        $bob = $this->createGDPRAckedUser();
        $this->checkin("Hamburg Hbf", $timestamp, $bob);

        // THEN: Alice should see that in their notification
        $notifications = $this->actingAs($alice)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // One other user on that train
        $notifications->assertJsonFragment([
                                               'type'            => UserJoinedConnection::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $alice->id
                                           ]);

        /** Deleting A Status Should Remove The UserJoinedConnection Notification. */

        // WHEN: Bob deletes their status
        $bob->statuses->first()->delete();

        // THEN: The notification should be gone.
        $notifications = $this->actingAs($alice)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(0); // no other user no more
    }

    /** @test */
    public function mark_notification_as_read(): void {
        // GIVEN: Alice has a notification
        $userToFollow = $this->createGDPRAckedUser();
        UserBackend::createFollow($this->user, $userToFollow);

        // GIVEN: Alice receives the notification and it's unread
        $notificationPart1 = DatabaseNotification::all()->where('notifiable_id', $userToFollow->id)->first();
        $notifyID          = $notificationPart1->id;
        $this->assertTrue($notificationPart1->read_at == null);

        // WHEN: toggleReadState is called
        $readReq = $this->actingAs($userToFollow)
                        ->post(route('notifications.toggleReadState', ['id' => $notifyID]));
        $readReq->assertStatus(201); // Created

        // THEN: the notification is read
        $notificationPart2 = json_decode($readReq->content());
        $this->assertFalse($notificationPart2->read_at == null);

        // WHEN: toggleReadState is called again
        $unreadReq = $this->actingAs($userToFollow)
                          ->post(route('notifications.toggleReadState', ['id' => $notifyID]));
        $unreadReq->assertStatus(202); // Created

        // THEN: the notification is marked as unread again
        $notificationPart3 = json_decode($unreadReq->content());
        $this->assertTrue($notificationPart3->read_at == null);
    }

    /** @test */
    public function deleting_a_user_should_delete_its_notifications(): void {
        // Given: Users Alice and Bob
        $alice = $this->user;
        $bob   = $this->createGDPRAckedUser();

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // Then: Bob should see that in their notifications
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one follow
        $notifications->assertJsonFragment([
                                               'type'            => UserFollowed::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $bob->id
                                           ]);

        // When: Bob deletes its account
        $delete = $this->actingAs($bob)
                       ->post(route('account.destroy'), [
                           'confirmation' => $bob->username
                       ]);
        $delete->assertStatus(302);
        $delete->assertRedirect('/');

        // Then: The notification should be gone, hence the notifications table is empty
        $notifications = DatabaseNotification::all();
        $this->assertEquals(0, $notifications->count());
    }
}
