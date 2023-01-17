<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Passport\Client;

/**
 * @mixin Builder
 */
class Webhook extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'oauth_client_id', 'url', 'secret'];
    protected $hidden   = ['oauth_client_id', 'secret', 'created_at', 'updated_at'];
    protected $appends  = ['events'];
    protected $casts    = [
        'id'              => 'integer',
        'oauth_client_id' => 'string',
        'url'             => 'string',
        'secret'          => 'string',
        'user_id'         => 'integer'
    ];

    public function client(): BelongsTo {
        return $this->belongsTo(Client::class, 'oauth_client_id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany {
        return $this->hasMany(WebhookEvent::class, 'webhook_id');
    }

    public function getEventsAttribute(): array {
        return $this->events()->get()->map(function($event) {
            return $event->event;
        })->toArray();
    }
}
