<?php

namespace Tests;

use App\Http\Controllers\Backend\WebhookController;
use App\Models\User;
use App\Models\Webhook;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\ClientRepository;

abstract class WebhookTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('passport:install');
        $this->artisan('passport:keys', ['--no-interaction' => true]);
        $this->artisan('db:seed');
    }
    public function createClient(User $user): PassportClient
    {
        $clients = new ClientRepository();
        return $clients->create($user->id, "TRWL Testing Application", "https://example.com", null, false, false, true);
    }

    public function createWebhook(User $user, PassportClient $client, array $events): Webhook
    {
        $bitflag = 0;
        foreach ($events as $event) {
            $bitflag |= $event->value;
        }
        $request = WebhookController::createWebhookRequest($user, $client, 'stub', "https://example.com", $bitflag);
        return WebhookController::createWebhook($request);
    }
}
