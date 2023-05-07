<?php

namespace Tests\Feature\APIv1;

use App\Http\Controllers\UserController as UserBackend;
use App\Models\Status;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;

class LikesTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testCreateShowAndDestroyLike(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $status    = Status::factory()->create();

        $this->assertDatabaseMissing('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->postJson(
            uri:     '/api/v1/like/' . $status->id,
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);

        //Should fail: Already liked
        $response = $this->postJson(
            uri:     '/api/v1/like/' . $status->id,
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertStatus(409);

        $response = $this->get(
            uri:     '/api/v1/statuses/' . $status->id . '/likedby',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               '*' => [
                                                   'id',
                                                   'displayName',
                                                   'username',
                                                   'profilePicture',
                                                   'trainDistance',
                                                   'trainDuration',
                                                   'trainSpeed',
                                                   'points',
                                                   'twitterUrl',
                                                   'mastodonUrl',
                                                   'privateProfile',
                                                   'preventIndex',
                                                   'userInvisibleToMe',
                                                   'muted',
                                                   'following',
                                                   'followPending',
                                               ]
                                           ]
                                       ]);
        $this->assertCount(1, $response->json('data'));

        $response = $this->deleteJson(
            uri:     '/api/v1/like/' . $status->id,
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);
    }

    public function testDestroyShouldFailIfWrongStatusIdGiven() {
        $response = $this->deleteJson(
            uri:     '/api/v1/like/999999999', //some high id which should not exist in our test-data
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertNotFound();
    }

    public function testNobodySeesLikesIfStatusAuthorHasDisabledLikes() {
        $bob      = User::factory()->create();
        $bobToken = $bob->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $status     = Status::factory()->create();
        $alice      = $status->user;
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $alice->update(["likes_enabled" => false]);

        $response = $this->postJson(
            uri:     '/api/v1/like/' . $status->id,
            headers: ['Authorization' => 'Bearer ' . $bobToken]
        );
        $response->assertCreated();

        $this->assertSeeNumberOfLikes($status, $bobToken, 0);
        $this->assertSeeNumberOfLikes($status, $aliceToken, 0);
    }

    public function testBobDoesntSeeLikesIfBobHasDisabledLikes() {
        $bob      = User::factory()->create();
        $bobToken = $bob->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $status     = Status::factory()->create();
        $alice      = $status->user;
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;


        $bob->update(["likes_enabled" => false]);

        $response = $this->postJson(
            uri:     '/api/v1/like/' . $status->id,
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertCreated();

        $this->assertSeeNumberOfLikes($status, $bobToken, 0);
        $this->assertSeeNumberOfLikes($status, $aliceToken, 1);
    }

    private function assertSeeNumberOfLikes($status, $bobToken, $expectedLikeCount): void {
        $response = $this->get(
            uri:     '/api/v1/statuses/' . $status->id . '/likedby',
            headers: ['Authorization' => 'Bearer ' . $bobToken]
        );
        $response->assertOk();
        $this->assertCount($expectedLikeCount, $response->json('data'));
    }
}
