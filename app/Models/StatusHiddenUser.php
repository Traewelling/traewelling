<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for users who are hidden from viewing specific statuses.
 * This is the inverse of TrustedUser - instead of allowing specific users,
 * it blocks specific users from seeing a particular status.
 */
class StatusHiddenUser extends Model
{
    use HasUuids;

    protected $keyType      = 'string';
    public    $incrementing = false;
    protected $fillable     = ['status_id', 'user_id'];
    protected $casts        = [
        'id'        => 'string',
        'status_id' => 'integer',
        'user_id'   => 'integer',
    ];

    /**
     * Get the status that this hidden user entry belongs to.
     */
    public function status(): BelongsTo {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    /**
     * Get the user who is hidden from viewing the status.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
