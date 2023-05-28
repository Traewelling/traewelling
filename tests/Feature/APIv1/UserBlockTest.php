<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserBlockTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCanBeBlockedOnceAndThenUnblocked(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $bob        = User::factory()->create();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        //Already blocked -> expect 409
        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertConflict();

        //Now unblock user
        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('user_blocks', [
            'user_id'    => $alice->id,
            'blocked_id' => $bob->id,
        ]);

        //Now unblock an already unblocked user and expect 409
        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertConflict();
    }

    public function testNonExistingUserCantBeBlocked(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => 9999]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertNotFound();
    }

    public function testNonExistingUserCantBeUnblocked(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/block', [':userId' => 9999]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertNotFound();
    }
}
