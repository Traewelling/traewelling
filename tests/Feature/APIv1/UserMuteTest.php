<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserMuteTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserCanBeMutedOnceAndThenUnmuted(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $bob        = User::factory()->create();

        $this->assertDatabaseMissing('user_mutes', [
            'user_id'  => $alice->id,
            'muted_id' => $bob->id,
        ]);

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('user_mutes', [
            'user_id'  => $alice->id,
            'muted_id' => $bob->id,
        ]);

        //Already muted -> expect 409
        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertConflict();

        //Now unmute user
        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('user_mutes', [
            'user_id'  => $alice->id,
            'muted_id' => $bob->id,
        ]);

        //Now unmute an already unmuted user and expect 409
        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/mute', [':userId' => $bob->id]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertConflict();
    }

    public function testNonExistingUserCantBeMuted(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     strtr('/api/v1/user/:userId/mute', [':userId' => 9999]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertNotFound();
    }

    public function testNonExistingUserCantBeUnmuted(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->deleteJson(
            uri:     strtr('/api/v1/user/:userId/mute', [':userId' => 9999]),
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertNotFound();
    }
}
