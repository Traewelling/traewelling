<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MastodonServer extends Model
{

    protected $fillable = [
        'domain',
        'client_id',
        'client_secret'
    ];
}
