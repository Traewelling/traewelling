<?php

namespace App\Repositories;

use App\Models\OAuthClient;
use Laravel\Passport\Passport;
use Illuminate\Support\Str;

class OAuthClientRepository {
    // Based on Passports's code: https://github.com/laravel/passport/blob/d8cc34766635da552a9ddff80248c5505f19bd04/src/ClientRepository.php#L140-L156
    /**
     * Store a new client.
     */
    public function create(
        int $userId,
        string $name,
        string $redirect,
        string|null $provider = null,
        bool $personalAccess = false,
        bool $password = false,
        bool $confidential = true,
        string|null $privacyPolicyUrl = null,
        bool $webhooksEnabled = false,
        string|null $authorizedWebhookUrl = null,
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
        string|null $privacyPolicyUrl,
        bool $webhooksEnabled,
        string|null $authorizedWebhookUrl
    ): OAuthClient {
        $client->forceFill([
            'name' => $name,
            'redirect' => $redirect,
            'privacy_policy_url' => $privacyPolicyUrl,
            'webhooks_enabled' => $webhooksEnabled,
            'authorized_webhook_url' => $authorizedWebhookUrl,
        ])->save();

        return $client;
    }

    public function findForUser(int $clientId, int $userId): OAuthClient {
        $client = Passport::client();

        return $client
            ->where($client->getKeyName(), $clientId)
            ->where('user_id', $userId)
            ->first();
    }

    public function find(int $id): OAuthClient {
        $client = Passport::client();

        return $client->where($client->getKeyName(), $id)->first();
    }
}
