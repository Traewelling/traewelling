<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMute extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'muted_id'];
    protected $casts    = [
        'id'       => 'integer',
        'user_id'  => 'integer',
        'muted_id' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mutedUser(): BelongsTo {
        return $this->belongsTo(User::class, 'muted_id', 'id');
    }
}
