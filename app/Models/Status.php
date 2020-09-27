<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Status extends Model {

    use HasFactory;

    protected $hidden = ['user_id', 'business'];

    protected $appends = ['favorited'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function likes() {
        return $this->hasMany(Like::class);
    }

    public function trainCheckin() {
        return $this->hasOne(TrainCheckin::class);
    }

    public function event() {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function getFavoritedAttribute() {
        return !!$this->likes->where('user_id', Auth::id())->first();
    }

}
