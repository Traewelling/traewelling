<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\WebhookEvent as WebhookEventEnum;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * 
 *
 * @property int $webhook_id
 * @property WebhookEventEnum $event
 * @property-read \App\Models\Webhook|null $webhook
 * @method static Builder<static>|WebhookEvent newModelQuery()
 * @method static Builder<static>|WebhookEvent newQuery()
 * @method static Builder<static>|WebhookEvent query()
 * @method static Builder<static>|WebhookEvent whereEvent($value)
 * @method static Builder<static>|WebhookEvent whereWebhookId($value)
 * @mixin \Eloquent
 */
class WebhookEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['webhook_id', 'event'];
    protected $casts    = [
        'webhook_id' => 'integer',
        'event'      => WebhookEventEnum::class,
    ];

    public function webhook(): HasOne {
        return $this->hasOne(Webhook::class, 'id', 'webhook_id');
    }
}
