<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\Backend\User\FollowController;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;

class FollowTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testCreateAndListFollow(): void {
        $user1      = User::factory()->create();
        Passport::actingAs($user1, ['*']);
        $user2      = User::factory()->create();

        $this->assertDatabaseMissing('follows', [
            'user_id'   => $user1->id,
            'follow_id' => $user2->id,
        ]);

        $response = $this->postJson(sprintf('/api/v1/user/%s/follow', $user2->id));
        $response->assertCreated();

        $this->assertDatabaseHas('follows', [
            'user_id'   => $user1->id,
            'follow_id' => $user2->id,
        ]);

        //User 1 shouldn't have followers...
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
        $response = $this->get('/api/v1/user/self/followings');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function testDestroyFollow(): void {
        $user1      = User::factory()->create();
        // ToDo: I wasn't able to move this to Passport::actingAs() -- the first response is always 409
        $user1token = $user1->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $user2      = User::factory()->create();
        FollowController::createOrRequestFollow($user1, $user2);

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
