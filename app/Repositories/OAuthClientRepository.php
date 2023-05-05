<?php

namespace App\Repositories;

use App\Models\OAuthClient;
use App\Models\Webhook;
use Laravel\Passport\Passport;
use Illuminate\Support\Str;
use Laravel\Passport\Token;

// Based on Passports's code:
// https://github.com/laravel/passport/blob/d8cc34766635da552a9ddff80248c5505f19bd04/src/ClientRepository.php#L140-L156
class OAuthClientRepository
{
    /**
     * Store a new client.
     */
    public function create(
        int         $userId,
        string      $name,
        string      $redirect,
        string|null $provider = null,
        bool        $personalAccess = false,
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
        string|null $authorizedWebhookUrl,
        bool $confidential = true,
    ): OAuthClient {
        $secret = $client->secret;
        if ($client->isConfidential() != $confidential) {
            if ($secret == null) {
                $secret = Str::random(40);
            } else {
                $secret = null;
            }
        }

        $client->forceFill([
            'name' => $name,
            'redirect' => $redirect,
            'privacy_policy_url' => $privacyPolicyUrl,
            'webhooks_enabled' => $webhooksEnabled,
            'authorized_webhook_url' => $authorizedWebhookUrl,
            'secret' => $secret,
        ])->save();

        Token::where('client_id', $client->id)->update(['revoked' => true]);

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

    public function hasWebhooks(int $id): bool {
        $webhooks = (new Webhook)->where('oauth_client_id', $id)->get();
        return $webhooks->count() > 0;
    }
}
