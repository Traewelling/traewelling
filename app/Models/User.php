<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail {

    use Notifiable, HasApiTokens, HasFactory;

    protected $fillable = [
        'username', 'name', 'avatar', 'email', 'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email',
        'email_verified_at',
        'privacy_ack_at',
        'created_at',
        'updated_at',
        'home_id',
        'avatar',
        'always_dbl',
        'role',
        'social_profile'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function averageSpeed() {
        return $this->train_duration == 0 ? 0 : $this->train_distance / ($this->train_duration / 60);
    }

    public function socialProfile() {
        return $this->hasOne(SocialLoginProfile::class);
    }

    public function statuses() {
        return $this->hasMany(Status::class);
    }

    public function home() {
        return $this->hasOne(TrainStations::class, 'id', 'home_id');
    }

    public function likes() {
        return $this->hasMany(Like::class);
    }

    public function follows() {
        return $this->hasMany(Follow::class);
    }

    public function followers() {
        return $this->hasMany(Follow::class, 'follow_id', 'id');
    }

    public function sessions() {
        return $this->hasMany(Session::class);
    }


}
