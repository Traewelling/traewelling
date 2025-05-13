<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{

    protected $keyType = 'string';

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
