<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Token;
use Tests\ApiTestCase;

class PasswordChangeTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->putJson('/api/v1/settings/password', [
            'currentPassword' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_without_password_can_set_password(): void
    {
        $user = User::factory()->create(['password' => null]);
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->putJson('/api/v1/settings/password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $this->actAsApiUserWithAllScopes($user);

        $response = $this->putJson('/api/v1/settings/password', [
            'currentPassword' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertUnprocessable();
    }

    public function test_password_change_requires_auth(): void
    {
        $response = $this->putJson('/api/v1/settings/password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_third_party_oauth_client_cannot_change_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);

        // Create a personal access token (valid JWT + DB record)
        $tokenResult = $user->createToken('test');

        // Swap the client_id to a non-personal-access OAuth client
        $thirdPartyClient = $this->createOAuthClient($user, true);
        Token::where('id', $tokenResult->token->id)->update(['client_id' => $thirdPartyClient->id]);

        $response = $this->putJson(
            '/api/v1/settings/password',
            [
                'currentPassword' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ],
            ['Authorization' => 'Bearer ' . $tokenResult->accessToken],
        );

        $response->assertForbidden();
    }
}
