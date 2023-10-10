<?php

namespace Tests\Feature\APIv1;

use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class LikesTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testCreateShowAndDestroyLike(): void {
        $user      = User::factory()->create();
        Passport::actingAs($user, ['*']);
        $checkin   = TrainCheckin::factory()->create();
        $status    = $checkin->status;

        $this->assertDatabaseMissing('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->postJson(
            uri:     '/api/v1/status/' . $status->id . '/like',
        );
        $response->assertCreated();

        $this->assertDatabaseHas('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);

        //Should fail: Already liked
        $response = $this->postJson('/api/v1/status/' . $status->id . '/like');
        $response->assertStatus(409);

        $response = $this->get('/api/v1/status/' . $status->id . '/likes');
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

        $response = $this->deleteJson('/api/v1/status/' . $status->id . '/like');
        $response->assertOk();

        $this->assertDatabaseMissing('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);
    }

    public function testDestroyShouldFailIfWrongStatusIdGiven() {
        $this->actAsApiUserWithAllScopes();
        $response = $this->deleteJson(
            uri:     '/api/v1/like/999999999', //some high id which should not exist in our test-data
        );
        $response->assertNotFound();
    }

    public function testCannotLikeIfStatusAuthorHasDisabledLikes(): void {
        $bob      = User::factory()->create();
        Passport::actingAs($bob, ['*']);

        $status     = Status::factory()->create();
        $alice      = $status->user;
        $alice->update(["likes_enabled" => false]);

        $response = $this->postJson('/api/v1/status/' . $status->id . '/like');
        $response->assertStatus(403);

        $this->assertSeeNumberOfLikes($status, $bob, 0);
        Auth::forgetUser();
        $this->assertSeeNumberOfLikes($status, $alice, 0);
    }

    public function testBobDoesntSeeLikesIfBobHasDisabledLikes(): void {
        $checkin    = TrainCheckin::factory()->create();
        $alice      = $checkin->status->user;
        Passport::actingAs($alice, ['*']);

        $response = $this->postJson('/api/v1/status/' . $checkin->status->id . '/like');
        $response->assertCreated();
        $this->assertSeeNumberOfLikes($checkin->status, $alice, 1);

        Auth::forgetUser();

        $bob      = User::factory(["likes_enabled" => false])->create();
        $this->assertSeeNumberOfLikes($checkin->status, $bob, 0);
    }

    private function assertSeeNumberOfLikes(Status $status, User $user, int $expectedLikeCount): void {
        Passport::actingAs($user, ['*']);
        $response = $this->get('/api/v1/status/' . $status->id . '/likes');
        $response->assertOk();
        $this->assertCount($expectedLikeCount, $response->json('data'));
    }
}
