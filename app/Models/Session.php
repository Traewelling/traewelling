<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{

    protected $keyType = 'string';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
