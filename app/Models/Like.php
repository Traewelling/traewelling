<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'status_id'];
    protected $casts    = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'status_id' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

}
