<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AuthTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_access_with_revoked_token_is_not_possible(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $token->token->revoke();
        $this->assertGuest();
        $response = $this->get('/api/v1/auth/user', [
            'Authorization' => 'Bearer ' . $token->accessToken,
        ]);
        $response->assertUnauthorized();
    }

    public function test_access_with_valid_token_is_possible(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $this->assertGuest();
        $response = $this->get('/api/v1/auth/user', [
            'Authorization' => 'Bearer ' . $token->accessToken,
        ]);
        $response->assertOk();
    }
}
