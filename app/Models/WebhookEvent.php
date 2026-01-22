<?php

namespace App\Models;

use App\Enum\WebhookEvent as WebhookEventEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $webhook_id
 * @property WebhookEventEnum $event
 * @property-read \App\Models\Webhook|null $webhook
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent whereEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookEvent whereWebhookId($value)
 *
 * @mixin \Eloquent
 */
class WebhookEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['webhook_id', 'event'];

    protected $casts = [
        'webhook_id' => 'integer',
        'event' => WebhookEventEnum::class,
    ];

    public function webhook(): HasOne
    {
        return $this->hasOne(Webhook::class, 'id', 'webhook_id');
    }
}
