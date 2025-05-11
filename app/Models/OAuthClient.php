<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Laravel\Passport\AuthCode;
use Laravel\Passport\Client as PassportClient;
use Laravel\Passport\Database\Factories\ClientFactory;
use Laravel\Passport\Token;

/**
 * 
 *
 * @property int                            $id
 * @property int|null                       $user_id
 * @property string                         $name
 * @property string|null                    $secret
 * @property string|null                    $provider
 * @property string                         $redirect
 * @property int                            $webhooks_enabled
 * @property string|null                    $privacy_policy_url
 * @property string|null                    $authorized_webhook_url
 * @property bool                           $personal_access_client
 * @property bool                           $password_client
 * @property bool                           $revoked
 * @property Carbon|null                    $created_at
 * @property Carbon|null                    $updated_at
 * @property-read Collection<int, AuthCode> $authCodes
 * @property-read int|null                  $auth_codes_count
 * @property-read string|null               $plain_secret
 * @property-read Collection<int, Token>    $tokens
 * @property-read int|null                  $tokens_count
 * @property-read User|null                 $user
 * @method static ClientFactory factory($count = null, $state = [])
 * @method static Builder<static>|OAuthClient newModelQuery()
 * @method static Builder<static>|OAuthClient newQuery()
 * @method static Builder<static>|OAuthClient query()
 * @method static Builder<static>|OAuthClient whereAuthorizedWebhookUrl($value)
 * @method static Builder<static>|OAuthClient whereCreatedAt($value)
 * @method static Builder<static>|OAuthClient whereId($value)
 * @method static Builder<static>|OAuthClient whereName($value)
 * @method static Builder<static>|OAuthClient wherePasswordClient($value)
 * @method static Builder<static>|OAuthClient wherePersonalAccessClient($value)
 * @method static Builder<static>|OAuthClient wherePrivacyPolicyUrl($value)
 * @method static Builder<static>|OAuthClient whereProvider($value)
 * @method static Builder<static>|OAuthClient whereRedirect($value)
 * @method static Builder<static>|OAuthClient whereRevoked($value)
 * @method static Builder<static>|OAuthClient whereSecret($value)
 * @method static Builder<static>|OAuthClient whereUpdatedAt($value)
 * @method static Builder<static>|OAuthClient whereUserId($value)
 * @method static Builder<static>|OAuthClient whereWebhooksEnabled($value)
 * @mixin Eloquent
 */
class OAuthClient extends PassportClient
{
    use HasFactory;

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
        'personal_access_client' => 'bool',
        'password_client'        => 'bool',
        'revoked'                => 'bool',
    ];

    protected $hidden = [
        'secret',
    ];

    public static function newFactory() {
        return parent::newFactory();
    }

    public function isConfidential(): bool {
        return $this->secret != null;
    }
}
