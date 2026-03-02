<?php

namespace App\Repositories;

use App\Models\OAuthClient;
use App\Models\Webhook;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;

// Based on Passports's code:
// https://github.com/laravel/passport/blob/d8cc34766635da552a9ddff80248c5505f19bd04/src/ClientRepository.php#L140-L156
class OAuthClientRepository
{
    /**
     * Store a new client.
     */
    public function create(
        int $userId,
        string $name,
        string $redirect,
        ?string $provider = null,
        bool $personalAccess = false,
        bool $password = false,
        bool $confidential = true,
        ?string $privacyPolicyUrl = null,
        bool $webhooksEnabled = false,
        ?string $authorizedWebhookUrl = null,
    ): OAuthClient {
        $client = Passport::client()->forceFill([
            'user_id' => $userId,
            'name' => $name,
            'secret' => ($confidential || $personalAccess) ? Str::random(40) : null,
            'provider' => $provider,
            'redirect' => $redirect,
            'personal_access_client' => $personalAccess,
            'password_client' => $password,
            'revoked' => false,
            'privacy_policy_url' => $privacyPolicyUrl,
            'webhooks_enabled' => $webhooksEnabled,
            'authorized_webhook_url' => $authorizedWebhookUrl,
        ]);

        $client->save();

        return $client;
    }

    /**
     * Update the given client.
     */
    public function update(
        OAuthClient $client,
        string $name,
        string $redirect,
        ?string $privacyPolicyUrl,
        bool $webhooksEnabled,
        ?string $authorizedWebhookUrl,
        bool $confidential = true,
    ): OAuthClient {
        $fill = [
            'name' => $name,
            'redirect' => $redirect,
            'privacy_policy_url' => $privacyPolicyUrl,
            'webhooks_enabled' => $webhooksEnabled,
            'authorized_webhook_url' => $authorizedWebhookUrl,
        ];

        // Only modify the secret when the confidentiality status actually changes,
        // to avoid re-hashing an already-hashed secret (v13 auto-hashes via mutator).
        if ($client->isConfidential() !== $confidential) {
            $fill['secret'] = $confidential ? Str::random(40) : null;
        }

        $client->forceFill($fill)->save();

        return $client;
    }

    public function findForUser(int $clientId, int $userId): OAuthClient
    {
        $client = Passport::client();

        return $client
            ->where($client->getKeyName(), $clientId)
            ->where('user_id', $userId)
            ->first();
    }

    public function find(int $id): OAuthClient
    {
        $client = Passport::client();

        return $client->where($client->getKeyName(), $id)->first();
    }

    public function hasWebhooks(int $id): bool
    {
        $webhooks = (new Webhook())->where('oauth_client_id', $id)->get();

        return $webhooks->count() > 0;
    }
}
