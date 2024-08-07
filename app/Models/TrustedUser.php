<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * // properties
 * @property string      id
 * @property integer     user_id
 * @property integer     trusted_id
 * @property Carbon|null expires_at
 * @property Carbon      created_at
 * @property Carbon      updated_at
 *
 * // relationships
 * @property User        user
 * @property User        trusted
 */
class TrustedUser extends Model
{
    use HasUuids;

    protected $keyType      = 'string';
    public    $incrementing = false;
    protected $fillable     = ['user_id', 'trusted_id', 'expires_at'];
    protected $casts        = [
        'id'         => 'string',
        'user_id'    => 'integer',
        'trusted_id' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function trusted(): BelongsTo {
        return $this->belongsTo(User::class, 'trusted_id', 'id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
