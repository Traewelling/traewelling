<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedUser extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['user_id', 'trusted_id', 'expires_at'];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'integer',
        'trusted_id' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function trusted(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trusted_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
