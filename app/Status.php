<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Status extends Model
{
    protected $hidden = ['user_id', 'business'];

    protected $appends = ['favorited'];

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

    public function getFavoritedAttribute() {
        return !!$this->likes->where('user_id', Auth::id())->first();
    }

}
