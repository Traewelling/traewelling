<?php

namespace Tests\Feature;

use App\Http\Controllers\Backend\User\FollowController;
use App\Http\Controllers\UserController;
use App\Models\User;
use App\Notifications\FollowRequestApproved;
use App\Notifications\FollowRequestIssued;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class PrivateProfileFollowerRelationsTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $alice;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->update(["private_profile" => true]);
        $this->alice = User::factory()->create();
    }

    public function testRequestPrivateFollowShouldCreateARequestNotification(): void {
        //create a user with a private profile
        $alice = User::factory()->create();
        $bob   = User::factory(['private_profile' => true])->create();

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        //alice requests to follow bob
        FollowController::createOrRequestFollow($alice, $bob);

        //check if bob has a notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $bob->id,
            'type'          => FollowRequestIssued::class,
        ]);
    }

    public function testAcceptingAFollowRequestShouldSpawnANotificationForInitiator(): void {
        //create a user with a private profile
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $bob        = User::factory(['private_profile' => true])->create();
        $bobToken   = $bob->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        //check that there are no notifications
        $this->assertDatabaseCount('notifications', 0);

        //alice requests to follow bob
        FollowController::createOrRequestFollow($alice, $bob);

        //bob should have a notification
        $response = $this->get(
            uri:     '/api/v1/notifications',
            headers: ['Authorization' => 'Bearer ' . $bobToken]
        );
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
        $response->assertJsonFragment(['type' => 'FollowRequestIssued']);

        //bob accepts the request
        FollowController::approveFollower($bob->id, $alice->id);

        //alice should have a notification
        $response = $this->get(
            uri:     '/api/v1/notifications',
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertOk();
        $response->assertJsonCount(1, 'data'); // one notification
    }

    public function testAcceptingAFollowRequestShouldMakeAProfileVisible(): void {
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
