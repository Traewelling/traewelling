<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Passport;

/**
 * @property string $id
 * @property int $user_id
 * @property int $oauth_client_id
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon $expires_at
 * @property string $events
 * @property string $url
 * @property-read OAuthClient $client
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereEvents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereOauthClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCreationRequest whereUserId($value)
 *
 * @mixin \Eloquent
 */
class WebhookCreationRequest extends Model
{
    public $timestamps = false;

    protected $fillable = ['id', 'user_id', 'oauth_client_id', 'revoked', 'expires_at', 'events', 'url'];

    protected $hidden = ['url'];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'integer',
        'oauth_client_id' => 'integer',
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
        'events' => 'string',
        'url' => 'string',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Passport::clientModel(), 'oauth_client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return Carbon::now() > $this->expires_at;
    }
}
