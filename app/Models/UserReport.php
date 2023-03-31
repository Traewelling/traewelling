<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = ['reporter_id', 'user_id', 'message'];
    protected $casts    = [
        'reporter_id' => 'integer',
        'user_id'     => 'integer',
        'message'     => 'string',
    ];

    public function reporter(): BelongsTo {
        return $this->belongsTo(User::class, 'reporter_id', 'id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
