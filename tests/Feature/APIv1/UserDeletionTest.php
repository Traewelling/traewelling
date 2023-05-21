<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserDeletionTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testUserAccountCanBeDeleted(): void {
        $alice      = User::factory()->create();
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $this->assertDatabaseHas('users', ['id' => $alice->id]);

        $response = $this->deleteJson(
            uri:     '/api/v1/settings/account',
            data:    ['confirmation' => $alice->username],
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $alice->id]);
    }
}
