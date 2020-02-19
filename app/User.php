<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

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
        'password', 'remember_token', 'email', 'email_verified_at', 'privacy_ack_at', 'created_at', 'updated_at', 'home_id', 'avatar', 'always_dbl'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function socialProfile()
    {
        return $this->hasOne(SocialLoginProfile::class);
    }

    public function statuses(){
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

    public function sessions() {
        return $this->hasMany('App\Session');
    }

}
