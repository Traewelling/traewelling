<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $hidden = ['user_id', 'business'];

    public function user() {
        return $this->belongsTo('App\User');
    }

    public function likes() {
        return $this->hasMany('App\Like');
    }

    public function trainCheckin() {
        return $this->hasOne('App\TrainCheckin');
    }

    public function event() {
        return $this->hasOne('App\Event', 'id', 'event_id');
    }

}
