<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enum\WebhookEvent;
use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\ApiTestCase;

class OAuthFlowTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * Create a first-party confidential client (no user_id) eligible for
     * the client_credentials grant.
     */
    private function createFirstPartyClient(): OAuthClient
    {
        /** @var OAuthClient $client */
        $client = Passport::client()->forceFill([
            'name' => 'Test Machine Client',
            'secret' => Str::random(40),
            'redirect' => '',
            'grant_types' => ['client_credentials'],
            'personal_access_client' => false,
            'password_client' => false,
            'revoked' => false,
            'privacy_policy_url' => null,
            'webhooks_enabled' => false,
            'authorized_webhook_url' => null,
        ]);
        $client->save();

        return $client;
    }

    /**
     * Drive the authorize → approve steps and return the authorization code.
     *
     * @param  array<string, string>  $extraParams  Extra query params for GET /oauth/authorize.
     */
    private function obtainAuthCode(User $user, OAuthClient $client, array $extraParams = []): string
    {
        $this->actingAs($user)
            ->get(route('oauth.authorizations.authorize', array_merge([
                'response_type' => 'code',
                'client_id' => $client->id,
                'redirect_uri' => 'https://example.com',
                'scope' => 'read-statuses',
                'state' => 'test-state',
            ], $extraParams)));

        $authToken = session('authToken');

        $approveResponse = $this->actingAs($user)
            ->post(route('oauth.authorizations.approve'), [
                'auth_token' => $authToken,
            ]);

        parse_str(parse_url($approveResponse->headers->get('Location'), PHP_URL_QUERY), $query);

        return $query['code'];
    }

    public function test_client_credentials_grant_issues_access_token(): void
    {
        $client = $this->createFirstPartyClient();

        $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_authorization_page_is_shown_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->actingAs($user)
            ->get(route('oauth.authorizations.authorize', [
                'response_type' => 'code',
                'client_id' => $client->id,
                'redirect_uri' => 'https://example.com',
                'scope' => 'read-statuses',
                'state' => 'test-state',
            ]))
            ->assertOk()
            ->assertSessionHas('authToken')
            ->assertSessionHas('authRequest');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->get(route('oauth.authorizations.authorize', [
            'response_type' => 'code',
            'client_id' => $client->id,
            'redirect_uri' => 'https://example.com',
            'scope' => 'read-statuses',
            'state' => 'test-state',
        ]))->assertRedirect();
    }

    public function test_approving_authorization_redirects_with_code(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);

        $this->actingAs($user)
            ->get(route('oauth.authorizations.authorize', [
                'response_type' => 'code',
                'client_id' => $client->id,
                'redirect_uri' => 'https://example.com',
                'scope' => 'read-statuses',
                'state' => 'test-state',
            ]));

        $approveResponse = $this->actingAs($user)
            ->post(route('oauth.authorizations.approve'), [
                'auth_token' => session('authToken'),
            ]);

        $approveResponse->assertRedirect();
        $this->assertStringContainsString('code=', $approveResponse->headers->get('Location'));
        $this->assertStringContainsString('state=test-state', $approveResponse->headers->get('Location'));
    }

    public function test_full_authorization_code_flow_issues_access_and_refresh_token(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $code = $this->obtainAuthCode($user, $client);

        $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'code' => $code,
            'redirect_uri' => 'https://example.com',
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'refresh_token']);
    }

    public function test_authorization_code_cannot_be_reused(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $code = $this->obtainAuthCode($user, $client);

        $payload = [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'code' => $code,
            'redirect_uri' => 'https://example.com',
        ];

        $this->postJson(route('oauth.authorizations.token'), $payload)->assertOk();
        $this->postJson(route('oauth.authorizations.token'), $payload)->assertStatus(400);
    }

    public function test_token_request_with_wrong_secret_is_rejected(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $code = $this->obtainAuthCode($user, $client);

        $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => 'wrong-secret',
            'code' => $code,
            'redirect_uri' => 'https://example.com',
        ])->assertStatus(401);
    }

    public function test_refresh_token_issues_new_access_token(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $code = $this->obtainAuthCode($user, $client);

        $tokenResponse = $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'code' => $code,
            'redirect_uri' => 'https://example.com',
        ])->assertOk()->json();

        $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'refresh_token',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'refresh_token' => $tokenResponse['refresh_token'],
        ])
            ->assertOk()
            ->assertJsonStructure(['access_token', 'refresh_token']);
    }

    public function test_authorization_page_shows_webhook_info_when_requested(): void
    {
        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);

        $this->actingAs($user)
            ->get(route('oauth.authorizations.authorize', [
                'response_type' => 'code',
                'client_id' => $client->id,
                'redirect_uri' => 'https://example.com',
                'scope' => 'read-statuses',
                'state' => 'test-state',
                'trwl_webhook_url' => self::EXAMPLE_WEBHOOK_URL,
                'trwl_webhook_events' => WebhookEvent::CHECKIN_CREATE->value,
            ]))
            ->assertOk()
            ->assertSessionHas('webhook');
    }

    public function test_token_response_includes_webhook_data_when_webhook_was_requested(): void
    {
        $user = User::factory()->create();
        $client = $this->createWebhookClient($user);
        $code = $this->obtainAuthCode($user, $client, [
            'trwl_webhook_url' => self::EXAMPLE_WEBHOOK_URL,
            'trwl_webhook_events' => WebhookEvent::CHECKIN_CREATE->value,
        ]);

        $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'code' => $code,
            'redirect_uri' => 'https://example.com',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'refresh_token',
                'webhook' => ['id', 'secret', 'url'],
            ])
            ->assertJsonPath('webhook.url', self::EXAMPLE_WEBHOOK_URL);

        $this->assertDatabaseHas('webhooks', [
            'user_id' => $user->id,
            'oauth_client_id' => $client->id,
            'url' => self::EXAMPLE_WEBHOOK_URL,
        ]);
    }

    public function test_token_response_contains_no_webhook_key_without_webhook_request(): void
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $code = $this->obtainAuthCode($user, $client);

        $this->postJson(route('oauth.authorizations.token'), [
            'grant_type' => 'authorization_code',
            'client_id' => $client->id,
            'client_secret' => $client->plainSecret,
            'code' => $code,
            'redirect_uri' => 'https://example.com',
        ])
            ->assertOk()
            ->assertJsonMissingPath('webhook');
    }
}
