<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\User\FollowController;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Notifications\FollowRequestIssued;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class PrivateProfileFollowerRelationsTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $alice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->update(['private_profile' => true]);
        $this->alice = User::factory()->create();
    }

    public function test_request_private_follow_should_create_a_request_notification(): void
    {
        // create a user with a private profile
        $alice = User::factory()->create();
        $bob = User::factory(['private_profile' => true])->create();

        // check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        // alice requests to follow bob
        FollowController::createOrRequestFollow($alice, $bob);

        // check if bob has a notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $bob->id,
            'type' => FollowRequestIssued::class,
        ]);
    }

    public function test_accepting_a_follow_request_should_spawn_a_notification_for_initiator(): void
    {
        // create a user with a private profile
        $alice = User::factory()->create();
        $bob = User::factory(['private_profile' => true])->create();

        // check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        // alice requests to follow bob
        FollowController::createOrRequestFollow($alice, $bob);

        Passport::actingAs($bob, ['*']);
        // bob should have a notification
        $response = $this->get('/api/v1/notifications');
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
        $response->assertJsonFragment(['type' => 'FollowRequestIssued']);

        // bob accepts the request
        FollowController::approveFollower($bob->id, $alice->id);

        Passport::actingAs($alice, ['*']);
        // alice should have a notification
        $response = $this->get('/api/v1/notifications');
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
    }

    public function test_accepting_a_follow_request_should_make_a_profile_visible(): void
    {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob = $this->user;

        // Alice cannot see Bob. API returns 403 with PRIVATE_PROFILE reason
        $this->assertFalse($alice->can('view', $bob));
        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'PRIVATE_PROFILE');

        // When: Alice follows Bob
        $request = $this->actingAs($alice)->post(route('follow.request'), ['follow_id' => $bob->id]);
        $request->assertStatus(201);
        $follow = $this->actingAs($bob)->post(route('settings.follower.approve'), ['user_id' => $alice->id]);
        $follow->assertStatus(302);
        $alice->refresh();
        $bob->refresh();
        $this->assertContains($alice->id, $bob->followers->pluck('user_id'));

        // Alice can see Bob. API returns 200
        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)->assertOk();
        $this->assertTrue($alice->can('view', $bob));
    }

    #[Test]
    public function declining_a_follow_request_should_keep_invisibility(): void
    {
        // Given: Users Alice and Bob
        $alice = $this->alice;
        $bob = $this->user;

        // Alice cannot see Bob. API returns 403 with PRIVATE_PROFILE reason
        $this->assertFalse($alice->can('view', $bob));
        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'PRIVATE_PROFILE');

        // When: Alice requests to follow Bob, but Bob declines
        $request = $this->actingAs($alice)->post(route('follow.request'), ['follow_id' => $bob->id]);
        $request->assertStatus(201);
        $follow = $this->actingAs($bob)->post(route('settings.follower.reject'), ['user_id' => $alice->id]);
        $follow->assertStatus(302);

        $alice->refresh();
        $bob->refresh();

        // Alice still cannot see Bob. API still returns 403
        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'PRIVATE_PROFILE');
        $this->assertFalse($alice->can('view', $bob));
    }

    #[Test]
    public function removing_a_follower_should_result_in_invisibility(): void
    {
        // Given: Alice is an approved follower of Bob's private profile
        $alice = $this->alice;
        $bob = $this->user;
        UserController::createFollow($alice, $bob, isApprovedRequest: true);
        $alice->refresh();
        $bob->refresh();

        // Alice can see Bob : she is an approved follower
        $this->assertTrue($alice->can('view', $bob));
        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)->assertOk();

        // When: Bob removes Alice from his followers
        $this->actingAs($bob)
            ->post(route('settings.follower.remove'), ['user_id' => $alice->id])
            ->assertStatus(302);

        // Alice can no longer see Bob : removing a follower restores invisibility
        $alice->refresh();
        $bob->refresh();
        $this->assertFalse($alice->can('view', $bob));
        Passport::actingAs($alice, ['*']);
        $this->getJson('/api/v1/user/' . $bob->username)
            ->assertForbidden()
            ->assertJsonPath('meta.reason', 'PRIVATE_PROFILE');
    }
}
