<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }


    public function likes() {
        return $this->hasMany('App\Like');
    }

}
