<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
