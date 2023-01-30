<?php

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;
use App\Providers\AuthServiceProvider;

class AuthTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testRegisterLoginAndLogout(): void {
        // 1. Register with failing validator
        $response = $this->postJson('/api/v1/auth/signup', [
            //Missing username -> validator should fail
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'thisisnotasecurepassword123',
            'password_confirmation' => 'thisisnotasecurepassword123',
        ]);
        $response->assertUnprocessable();

        // 2. Successful register
        $response = $this->postJson('/api/v1/auth/signup', [
            'username'              => 'john_doe',
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'thisisnotasecurepassword123',
            'password_confirmation' => 'thisisnotasecurepassword123',
        ]);
        $response->assertCreated();
        $response->assertJsonStructure([
                                           'data' => [
                                               'token',
                                               'expires_at',
                                           ]
                                       ]);

        // 3. Login with failing validator
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'something random',
            'password' => 'not the correct password',
        ]);
        $response->assertUnauthorized();

        // 4. Successful login
        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'john_doe',
            'password' => 'thisisnotasecurepassword123',
        ]);
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'token',
                                               'expires_at',
                                           ]
                                       ]);
        $token = $response->json()['data']['token'];

        // 5. See current user
        $response = $this->get('/api/v1/auth/user', [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertOk();
        $this->assertUserResource($response);
        $this->assertEquals('john_doe', $response->json('data.username'));

        // 6. Refresh token
        $response = $this->post('/api/v1/auth/refresh', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               'token',
                                               'expires_at',
                                           ]
                                       ]);

        // 7. Logout / Revoke token
        $response = $this->post('/api/v1/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertOk();
    }

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
}
