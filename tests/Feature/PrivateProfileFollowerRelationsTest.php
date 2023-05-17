<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use App\Notifications\FollowRequestApproved;
use App\Notifications\FollowRequestIssued;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateProfileFollowerRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $alice;

    protected function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->update(["private_profile" => true]);
        $this->alice = User::factory()->create();
    }

    /**
     * @test
     */
    public function request_private_follow_should_create_a_request_notification(): void {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob   = $this->user;

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.request'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // Then: Bob should see that in their notifications
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one follow
        $notifications->assertJsonFragment([
                                               'type'            => FollowRequestIssued::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $bob->id
                                           ]);
    }

    /**
     * @test
     */
    public function create_private_follow_should_create_a_request_notification(): void {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob   = $this->user;

        // When: Alice follows Bob
        $follow = $this->actingAs($alice)->post(route('follow.create'), ['follow_id' => $bob->id]);
        $follow->assertStatus(201);

        // Then: Bob should see that in their notifications
        $notifications = $this->actingAs($bob)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one follow
        $notifications->assertJsonFragment([
                                               'type'            => FollowRequestIssued::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $bob->id
                                           ]);
    }

    /**
     * @test
     */
    public function accepting_a_follow_request_should_spawn_a_notification_for_initiator(): void {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob   = $this->user;

        // When: Alice follows Bob
        $request = $this->actingAs($alice)->post(route('follow.request'), ['follow_id' => $bob->id]);
        $request->assertStatus(201);
        $follow = $this->actingAs($bob)->post(route('settings.follower.approve'), ['user_id' => $alice->id]);
        $follow->assertStatus(302);

        // Then: Bob should see that in their notifications
        $notifications = $this->actingAs($alice)
                              ->get(route('notifications.latest'));
        $notifications->assertOk();
        $notifications->assertJsonCount(1); // one follow
        $notifications->assertJsonFragment([
                                               'type'            => FollowRequestApproved::class,
                                               'notifiable_type' => User::class,
                                               'notifiable_id'   => (string) $alice->id
                                           ]);
    }

    /**
     * @test
     */
    public function accepting_a_follow_request_should_make_a_profile_visible(): void {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob   = $this->user;

        // Alice cannot see Bob
        $this->assertFalse($alice->can('view', $bob));
        $guest = $this->actingAs($alice)->get(route('profile', ["username" => $bob->username]));
        $guest->assertSee(__('profile.private-profile-text'));

        // When: Alice follows Bob
        $request = $this->actingAs($alice)->post(route('follow.request'), ['follow_id' => $bob->id]);
        $request->assertStatus(201);
        $follow = $this->actingAs($bob)->post(route('settings.follower.approve'), ['user_id' => $alice->id]);
        $follow->assertStatus(302);
        $alice->refresh();
        $bob->refresh();
        $this->assertContains($alice->id, $bob->followers->pluck('user_id'));

        // Alice can see Bob
        $guest = $this->actingAs($alice)->get(route('profile', ["username" => $bob->username]));
        $guest->assertDontSee(__('profile.private-profile-text'));
        $this->assertTrue($alice->can('view', $bob));
    }

    /**
     * @test
     */
    public function declining_a_follow_request_should_keep_invisibility(): void {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob   = $this->user;

        // Alice cannot see Bob
        $this->assertFalse($alice->can('view', $bob));
        $guest = $this->actingAs($alice)->get(route('profile', ["username" => $bob->username]));
        $guest->assertSee(__('profile.private-profile-text'));

        // When: Alice follows Bob
        $request = $this->actingAs($alice)->post(route('follow.request'), ['follow_id' => $bob->id]);
        $request->assertStatus(201);
        $follow = $this->actingAs($bob)->post(route('settings.follower.reject'), ['user_id' => $alice->id]);
        $follow->assertStatus(302);

        $alice->refresh();
        $bob->refresh();

        // Alice cannot see Bob
        $guest = $this->actingAs($alice)->get(route('profile', ["username" => $bob->username]));
        $guest->assertSee(__('profile.private-profile-text'));
        $this->assertFalse($alice->can('view', $bob));
    }

    /**
     * @test
     */
    public function removing_a_follower_should_result_in_invisibility(): void {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob   = $this->user;
        UserController::createFollow($alice, $bob);
        $alice->refresh();

        // Alice cannot see Bob
        // ToDo: This technically checks if Alice CANNOT see Bob. But.. she should here?
        $guest = $this->actingAs($alice)->get(route('profile', ["username" => $bob->username]));
        $guest->assertSee(__('profile.private-profile-text'));
        $invisible = $this->actingAs($alice)->user->getUserInvisibleToMeAttribute();
        $this->assertTrue($invisible);

        // When: Alice follows Bob
        $follow = $this->actingAs($bob)->post(route('settings.follower.remove'), ['user_id' => $alice->id]);
        $follow->assertStatus(302);

        // Alice cannot see Bob
        $alice->refresh();
        $invisible = $this->actingAs($alice)->user->getUserInvisibleToMeAttribute();
        $this->assertTrue($invisible);
        $guest = $this->actingAs($alice)->get(route('profile', ["username" => $bob->username]));
        $guest->assertSee(__('profile.private-profile-text'));
    }
}
