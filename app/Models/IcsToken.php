<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IcsToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'token', 'last_accessed'];
    protected $casts    = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'last_accessed' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
