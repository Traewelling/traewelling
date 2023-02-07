<?php

namespace App\Models;

use AwStudio\Bitflags\Casts\Bitflags;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Passport\Passport;

/**
 * @mixin Builder
 */
class Webhook extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'oauth_client_id', 'url', 'secret', 'events'];
    protected $hidden   = ['oauth_client_id', 'secret', 'created_at', 'updated_at'];
    protected $casts    = [
        'id'              => 'integer',
        'oauth_client_id' => 'string',
        'url'             => 'string',
        'secret'          => 'string',
        'user_id'         => 'integer',
        'events'          => Bitflags::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Passport::clientModel(), 'oauth_client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
