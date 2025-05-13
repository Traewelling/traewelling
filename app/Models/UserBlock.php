<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBlock extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'blocked_id'];
    protected $casts    = [
        'id'         => 'integer',
        'user_id'    => 'integer',
        'blocked_id' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function blockedUser(): BelongsTo {
        return $this->belongsTo(User::class, 'blocked_id', 'id');
    }
}
