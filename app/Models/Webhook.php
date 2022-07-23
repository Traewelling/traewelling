<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = ['external_id', 'access_token_id', 'user_id', 'url'];
    protected $casts    = [
        'external_id'     => 'uuid',
        'access_token_id' => 'string',
        'user_id'         => 'integer',
        'url'             => 'string',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
