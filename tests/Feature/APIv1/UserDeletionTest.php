<?php

declare(strict_types=1);

namespace Tests\Feature\APIv1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Tests\ApiTestCase;

class UserDeletionTest extends ApiTestCase
{
    use RefreshDatabase;

    public function test_user_account_can_be_deleted(): void
    {
        $alice = User::factory()->create();
        Passport::actingAs($alice, ['*']);

        $this->assertDatabaseHas('users', ['id' => $alice->id]);

        $response = $this->deleteJson(
            uri: '/api/v1/settings/account',
            data: ['confirmation' => $alice->username],
        );
        $response->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $alice->id]);
    }

    public function test_wrong_confirmation_is_rejected(): void
    {
        $alice = User::factory()->create();
        $this->actAsApiUserWithAllScopes($alice);

        $response = $this->deleteJson('/api/v1/settings/account', [
            'confirmation' => 'wrong-username',
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => $alice->id]);
    }

    public function test_account_deletion_requires_auth(): void
    {
        $response = $this->deleteJson('/api/v1/settings/account', [
            'confirmation' => 'someuser',
        ]);

        $response->assertUnauthorized();
    }

    public function test_third_party_oauth_client_cannot_delete_account(): void
    {
        $alice = User::factory()->create();

        // Create a personal access token (valid JWT + DB record)
        $tokenResult = $alice->createToken('test');

        // Swap the client_id to a non-personal-access OAuth client
        $thirdPartyClient = $this->createOAuthClient($alice, true);
        Token::where('id', $tokenResult->token->id)->update(['client_id' => $thirdPartyClient->id]);

        $response = $this->deleteJson(
            '/api/v1/settings/account',
            ['confirmation' => $alice->username],
            ['Authorization' => 'Bearer ' . $tokenResult->accessToken],
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $alice->id]);
    }
}
