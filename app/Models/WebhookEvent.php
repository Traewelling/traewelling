<?php

namespace App\Models;

use App\Enum\WebhookEvent as WebhookEventEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
