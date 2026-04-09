<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int|null $webhook_id
 * @property int $user_id
 * @property int $oauth_client_id
 * @property string $event
 * @property string $url
 * @property int $attempt
 * @property int|null $response_code null = connection error or timeout
 * @property Carbon $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog query()
 *
 * @property-read Webhook|null $webhook
 *
 * @method static \Database\Factories\WebhookCallLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereOauthClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereResponseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookCallLog whereWebhookId($value)
 *
 * @mixin \Eloquent
 */
class WebhookCallLog extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'webhook_id',
        'user_id',
        'oauth_client_id',
        'event',
        'url',
        'attempt',
        'response_code',
        'created_at',
    ];

    protected $casts = [
        'webhook_id' => 'integer',
        'user_id' => 'integer',
        'oauth_client_id' => 'integer',
        'attempt' => 'integer',
        'response_code' => 'integer',
        'created_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
