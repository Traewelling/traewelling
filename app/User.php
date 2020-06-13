<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'avatar', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
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
        return $this->hasMany('App\Status');
    }

    public function home() {
        return $this->hasOne('App\TrainStations', 'id', 'home_id');
    }

    public function likes() {
        return $this->hasMany('App\Like');
    }

    public function follows() {
        return $this->hasMany('App\Follow');
    }

    public function followers() {
        return $this->hasMany('App\Follow', 'follow_id', 'id');
    }

    public function sessions() {
        return $this->hasMany('App\Session');
    }



}
