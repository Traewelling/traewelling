<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAgent extends Model
{
    protected $fillable = ['user_agent'];
    protected $casts    = [
        'user_agent' => 'string',
    ];
}
