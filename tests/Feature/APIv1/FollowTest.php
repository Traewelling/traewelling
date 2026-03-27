<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\Backend\User\FollowController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class FollowTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_create_and_list_follow(): void
    {
        $user1 = User::factory()->create();
        Passport::actingAs($user1, ['*']);
        $user2 = User::factory()->create();

        $this->assertDatabaseMissing('follows', [
            'user_id' => $user1->id,
            'follow_id' => $user2->id,
        ]);

        $response = $this->postJson(sprintf('/api/v1/user/%s/follow', $user2->id));
        $response->assertCreated();

        $this->assertDatabaseHas('follows', [
            'user_id' => $user1->id,
            'follow_id' => $user2->id,
        ]);

        // User 1 shouldn't have followers...
        $response = $this->get('/api/v1/user/self/followers');
        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'path',
                'per_page',
                'to',
            ],
        ]);
        $this->assertCount(0, $response->json('data'));

        // ...but user1 should have one following.
        $response = $this->get('/api/v1/user/self/followings');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_destroy_follow(): void
    {
        $follower = User::factory()->create();
        $user = User::factory()->create();
        FollowController::createOrRequestFollow($follower, $user);
        FollowController::createOrRequestFollow($user, $follower);

        // Without this, $user1->follows won't contain the newly created follow...
        $follower->load('follows');

        // Authenticate with Passport
        Passport::actingAs($follower, ['*']);

        $response = $this->deleteJson(
            uri: strtr('/api/v1/user/:userId/follow', [':userId' => $user->id])
        );
        $response->assertOk();

        $this->assertDatabaseMissing('follows', [
            'user_id' => $follower->id,
            'follow_id' => $user->id,
        ]);

        $this->assertDatabaseHas('follows', [
            'user_id' => $user->id,
            'follow_id' => $follower->id,
        ]);

        $response = $this->deleteJson(
            uri: strtr('/api/v1/user/:userId/follow', [':userId' => $user->id])
        );
        $response->assertStatus(409);
    }

    public function test_remove_follower(): void
    {
        $follower = User::factory()->create();
        $user = User::factory()->create();
        FollowController::createOrRequestFollow($follower, $user);
        FollowController::createOrRequestFollow($user, $follower);

        $user->load('followers');

        $this->assertCount(1, $user->followers);

        Passport::actingAs($user, ['*']);

        $response = $this->deleteJson(
            uri: strtr('/api/v1/user/self/followers/:userId', [':userId' => $follower->id])
        );
        $response->assertOk();

        $this->assertDatabaseMissing('follows', [
            'user_id' => $follower->id,
            'follow_id' => $user->id,
        ]);

        $this->assertDatabaseHas('follows', [
            'user_id' => $user->id,
            'follow_id' => $follower->id,
        ]);
    }

    public function test_remove_follower_with_uuid(): void
    {
        $follower = User::factory()->create();
        $user = User::factory()->create();
        FollowController::createOrRequestFollow($follower, $user);
        FollowController::createOrRequestFollow($user, $follower);

        $user->load('followers');

        $this->assertCount(1, $user->followers);

        Passport::actingAs($user, ['*']);

        $response = $this->deleteJson(
            uri: strtr('/api/v1/user/self/followers/:userId', [':userId' => $follower->uuid])
        );
        $response->assertOk();

        $this->assertDatabaseMissing('follows', [
            'user_id' => $follower->id,
            'follow_id' => $user->id,
        ]);

        $this->assertDatabaseHas('follows', [
            'user_id' => $user->id,
            'follow_id' => $follower->id,
        ]);
    }
}
