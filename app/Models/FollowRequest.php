<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowRequest extends Model
{

    protected $fillable = ['user_id', 'follow_id'];
    protected $casts    = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'follow_id' => 'integer',
    ];

    /**
     * @return BelongsTo The user who initiated the request
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsTo The user who has to accept or deny the Request
     */
    public function requestedFollow(): BelongsTo {
        return $this->belongsTo(User::class, 'follow_id', 'id');
    }
}
