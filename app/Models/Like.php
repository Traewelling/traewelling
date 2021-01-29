<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{

    protected $fillable = ['user_id', 'status_id'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

}
