<?php

namespace App\Models;

use AwStudio\Bitflags\Casts\Bitflags;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Client;

/**
 * @mixin Builder
 */
class WebhookCreationRequest extends Model
{
    public $timestamps = false;
    protected $fillable = ['id', 'user_id', 'oauth_client_id', 'revoked', 'expires_at', 'events', 'url'];
    protected $casts = [
        'id' => 'string',
        'user_id' => 'integer',
        'oauth_client_id' => 'integer',
        'revoked' => 'boolean',
        'expires_at' => 'datetime',
        'events' => Bitflags::class,
        'url' => 'string',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'oauth_client_id');
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
