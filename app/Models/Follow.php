<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'follow_id'];
    protected $casts    = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'follow_id' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function following(): BelongsTo {
        return $this->belongsTo(User::class, 'follow_id', 'id');
    }
}
