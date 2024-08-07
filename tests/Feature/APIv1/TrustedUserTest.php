<?php

namespace Tests\Feature\APIv1;

use App\Enum\User\FriendCheckinSetting;
use App\Http\Controllers\Backend\User\FollowController;
use App\Models\TrustedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class TrustedUserTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testListPagination(): void {
        $user        = User::factory()->create();
        $trustedUser = User::factory()->count(12)->create();
        $this->actAsApiUserWithAllScopes($user);

        foreach ($trustedUser as $userToTrust) {
            $response = $this->postJson("/api/v1/user/self/trusted", ['userId' => $userToTrust->id]);
            $response->assertCreated();
        }

        // list trusted users
        $response = $this->getJson("/api/v1/user/self/trusted");
        $response->assertOk();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
                                           'data',
                                           'links' => ['first', 'last', 'prev', 'next'],
                                           'meta'  => ['path', 'per_page', 'next_cursor', 'prev_cursor'],
                                       ]);

        //try next cursor
        $nextCursorResponse = $this->getJson($response->json('links.next'));
        $nextCursorResponse->assertOk();

        $nextCursorResponse->dump();
        //TODO: why isn't the cursor working? Every request is showing from the beginning.
    }
    
    public function testIndexTrustedByUsers(): void {
        $user = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // create a friend which allow friend checkin
        $friend = User::factory()->create(['friend_checkin' => FriendCheckinSetting::FRIENDS]);
        FollowController::createOrRequestFollow($user, $friend);
        FollowController::createOrRequestFollow($friend, $user);

        // create a user which allow checkins by trusted users and trust the user (without expiration)
        $truster = User::factory()->create(['friend_checkin' => FriendCheckinSetting::LIST]);
        TrustedUser::create(['user_id' => $truster->id, 'trusted_id' => $user->id]);

        // create a user which allow checkins by trusted users and trust the user (with expiration)
        $truster2 = User::factory()->create(['friend_checkin' => FriendCheckinSetting::LIST]);
        TrustedUser::create(['user_id' => $truster2->id, 'trusted_id' => $user->id, 'expires_at' => now()->addDay()]);

        // when requesting the list of trusted by users, both users should be listed
        $response = $this->getJson("/api/v1/user/self/trusted-by");
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['id' => $friend->id]);
        $response->assertJsonFragment(['id' => $truster->id]);
        $response->assertJsonFragment(['id' => $truster2->id]);
    }

    public function testStoreAndDeleteTrustedUserForYourself(): void {
        $user        = User::factory()->create();
        $trustedUser = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // trust user
        $response = $this->postJson("/api/v1/user/{$user->id}/trusted", [
            'userId'    => $trustedUser->id,
            'expiresAt' => now()->addDay()->toIso8601String(),
        ]);
        $response->assertCreated();
        $this->assertDatabaseHas('trusted_users', ['user_id' => $user->id, 'trusted_id' => $trustedUser->id]);

        // list trusted users
        $response = $this->getJson("/api/v1/user/{$user->id}/trusted");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $trustedUser->id]);

        // test, that the cleanup script does not delete the trusted user
        $this->assertDatabaseCount('trusted_users', 1);
        $this->assertEquals(0, $this->artisan('app:clean-db:trusted-user'));
        $this->assertDatabaseCount('trusted_users', 1);

        $this->travel(2)->days();

        // should not list expired trusted users, even if in database.
        $response = $this->getJson("/api/v1/user/{$user->id}/trusted");
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
        $response->assertJsonMissing(['id' => $trustedUser->id]);

        // now the cleanup script should delete the trusted user
        $this->assertDatabaseCount('trusted_users', 1);
        $this->assertEquals(0, $this->artisan('app:clean-db:trusted-user'));
        $this->assertDatabaseCount('trusted_users', 0);
    }

    public function testStoreAndDeleteTrustedUserForOtherUsersAsNonAdmin(): void {
        $user        = User::factory()->create();
        $truster     = User::factory()->create();
        $trustedUser = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // trust user
        $response = $this->postJson("/api/v1/user/{$truster->id}/trusted", ['userId' => $trustedUser->id]);
        $response->assertForbidden();

        // list trusted users
        $response = $this->getJson("/api/v1/user/{$truster->id}/trusted");
        $response->assertForbidden();

        // untrust user
        $response = $this->deleteJson("/api/v1/user/{$truster->id}/trusted/{$trustedUser->id}");
        $response->assertForbidden();
    }

    public function testStoreAndDeleteTrustedUserForOtherUsersAsAdmin(): void {
        $user        = User::factory()->create()->assignRole('admin');
        $truster     = User::factory()->create();
        $trustedUser = User::factory()->create();
        $this->actAsApiUserWithAllScopes($user);

        // trust user
        $response = $this->postJson("/api/v1/user/{$truster->id}/trusted", ['userId' => $trustedUser->id]);
        $response->assertCreated();
        $this->assertDatabaseHas('trusted_users', ['user_id' => $truster->id, 'trusted_id' => $trustedUser->id]);

        // list trusted users
        $response = $this->getJson("/api/v1/user/{$truster->id}/trusted");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $trustedUser->id]);

        // untrust user
        $response = $this->deleteJson("/api/v1/user/{$truster->id}/trusted/{$trustedUser->id}");
        $response->assertNoContent();
        $this->assertDatabaseMissing('trusted_users', ['user_id' => $truster->id, 'trusted_id' => $trustedUser->id]);
    }
}

