<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Token;

/**
 * @property int $id
 * @property string|null $user_id
 * @property string $name
 * @property string|null $secret
 * @property string|null $provider
 * @property string $redirect
 * @property bool $personal_access_client
 * @property bool $password_client
 * @property bool $revoked
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $authorized_webhook_url
 * @property string|null $privacy_policy_url
 * @property bool $webhooks_enabled
 * @property-read Collection<int, Webhook> $webhooks
 * @property-read int|null $webhooks_count
 * @property-read bool|null $has_webhooks
 * @property-read Collection<int, AuthCode> $authCodes
 * @property-read int|null $auth_codes_count
 * @property-read string|null $plain_secret
 * @property-read Collection<int, Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read User|null $user
 *
 * @method static \Laravel\Passport\Database\Factories\ClientFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereAuthorizedWebhookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient wherePasswordClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient wherePersonalAccessClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient wherePrivacyPolicyUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereRedirect($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient whereWebhooksEnabled($value)
 *
 * @property-read array $grant_types
 * @property-read \Illuminate\Foundation\Auth\User $owner
 * @property-read array $redirect_uris
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OAuthClient existsIn(array $haystack)
 *
 * @mixin \Eloquent
 */
class OAuthClient extends PassportClient
{
    protected $fillable = [
        'user_id',
        'name',
        'secret',
        'redirect',
        'privacy_policy_url',
        'personal_access_client',
        'password_client',
        'revoked',
        'created_at',
        'updated_at',
        'webhooks_enabled',
        'authorized_webhook_url',
    ];

    protected $casts = [
        'grant_types' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
    ];

    protected $hidden = [
        'secret',
    ];

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'oauth_client_id');
    }

    public function isConfidential(): bool
    {
        return $this->secret != null;
    }
}
