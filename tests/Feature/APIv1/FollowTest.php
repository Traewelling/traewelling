<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;

class FollowTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testCreateAndListFollow(): void {
        $user1      = User::factory()->create();
        $user1token = $user1->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $user2      = User::factory()->create();

        $this->assertDatabaseMissing('follows', [
            'user_id'   => $user1->id,
            'follow_id' => $user2->id,
        ]);

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/follow', [':userId' => $user2->id]),
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('follows', [
            'user_id'   => $user1->id,
            'follow_id' => $user2->id,
        ]);

        //User 1 shouldn't have followers...
        $response = $this->get(
            uri:     '/api/v1/settings/followers',
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data',
                                           'links' => [
                                               'first',
                                               'last',
                                               'prev',
                                               'next',
                                           ],
                                           'meta'  => [
                                               'current_page',
                                               'from',
                                               'path',
                                               'per_page',
                                               'to',
                                           ]
                                       ]);
        $this->assertCount(0, $response->json('data'));

        //...but user1 should have one following.
        $response = $this->get(
            uri:     '/api/v1/settings/followings',
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function testDestroyFollow(): void {
        $user1      = User::factory()->create();
        $user1token = $user1->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $user2      = User::factory()->create();
        UserBackend::createOrRequestFollow($user1, $user2);

        $response = $this->delete(
            uri:     strtr('/api/v1/user/:userId/follow', [':userId' => $user2->id]),
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('follows', [
            'user_id'   => $user1->id,
            'follow_id' => $user2->id,
        ]);

        $response = $this->delete(
            uri:     strtr('/api/v1/user/:userId/follow', [':userId' => $user2->id]),
            headers: ['Authorization' => 'Bearer ' . $user1token]
        );
        $response->assertStatus(409);
    }
}
