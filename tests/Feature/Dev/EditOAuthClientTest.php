<?php

namespace Tests\Feature\Dev;

use App\Models\User;
use App\Repositories\OAuthClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;

use Tests\FeatureTestCase;

class EditOAuthClientTest extends FeatureTestCase
{
    use RefreshDatabase;

    public function test_o_auth_client_confidential_edit_toggle()
    {
        $user = User::factory()->create();
        $client = $this->createOAuthClient($user, true);
        $clients = new OAuthClientRepository();
        $originalSecret = $client->secret;

        $clients->update(
            $client,
            $client->name,
            $client->redirect,
            $client->privacy_policy_url,
            $client->webhooks_enabled,
            $client->authorized_webhook_url,
            false
        );

        assertEquals($client->isConfidential(), false);
        assertEquals($client->secret, null);

        $clients->update(
            $client,
            $client->name,
            $client->redirect,
            $client->privacy_policy_url,
            $client->webhooks_enabled,
            $client->authorized_webhook_url,
            true
        );
        assertEquals($client->isConfidential(), true);
        assertNotEquals($client->secret, $originalSecret);
        assertNotEquals($client->secret, null);
    }
}
