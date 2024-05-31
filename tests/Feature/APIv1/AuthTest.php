<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class AuthTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testAccessWithRevokedTokenIsNotPossible(): void {
        $user  = User::factory()->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $token->token->revoke();
        $this->assertGuest();
        $response = $this->get('/api/v1/auth/user', [
            'Authorization' => 'Bearer ' . $token->accessToken,
        ]);
        $response->assertUnauthorized();
    }

    public function testAccessWithValidTokenIsPossible(): void {
        $user  = User::factory()->create();
        $token = $user->createToken('token', array_keys(AuthServiceProvider::$scopes));
        $this->assertGuest();
        $response = $this->get('/api/v1/auth/user', [
            'Authorization' => 'Bearer ' . $token->accessToken,
        ]);
        $response->assertOk();
    }
}
