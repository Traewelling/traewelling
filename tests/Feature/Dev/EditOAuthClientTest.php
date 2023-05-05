<?php

namespace Tests\Feature\Dev;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\OAuthClientRepository;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;

class EditOAuthClientTest extends TestCase {
    use RefreshDatabase;

    public function testOAuthClientConfidentialEditToggle() {
        $user = $this->createGDPRAckedUser();
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
