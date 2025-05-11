<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;

/**
 * 
 *
 * @property int $id
 * @property string $oauth_client_id
 * @property int $user_id
 * @property string $url
 * @property string|null $secret
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\OAuthClient $client
 * @property-read Collection<int, \App\Models\WebhookEvent> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\User $user
 * @method static Builder<static>|Webhook newModelQuery()
 * @method static Builder<static>|Webhook newQuery()
 * @method static Builder<static>|Webhook query()
 * @method static Builder<static>|Webhook whereCreatedAt($value)
 * @method static Builder<static>|Webhook whereId($value)
 * @method static Builder<static>|Webhook whereOauthClientId($value)
 * @method static Builder<static>|Webhook whereSecret($value)
 * @method static Builder<static>|Webhook whereUpdatedAt($value)
 * @method static Builder<static>|Webhook whereUrl($value)
 * @method static Builder<static>|Webhook whereUserId($value)
 * @mixin \Eloquent
 */
class Webhook extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'oauth_client_id', 'url', 'secret'];
    protected $hidden   = ['oauth_client_id', 'secret', 'created_at', 'updated_at'];
    protected $casts    = [
        'id'              => 'integer',
        'oauth_client_id' => 'string',
        'url'             => 'string',
        'secret'          => 'string',
        'user_id'         => 'integer',
    ];

    public function client(): BelongsTo {
        return $this->belongsTo(Passport::clientModel(), 'oauth_client_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany {
        return $this->hasMany(WebhookEvent::class, 'webhook_id', 'id');
    }
}
