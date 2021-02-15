<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{

    use Notifiable, HasApiTokens, HasFactory;

    protected $fillable = [
        'username', 'name', 'avatar', 'email', 'password', 'home_id', 'private_profile'
    ];
    protected $hidden   = [
        'password', 'remember_token', 'email', 'email_verified_at', 'privacy_ack_at',
        'home_id', 'avatar', 'always_dbl', 'role', 'social_profile', 'created_at', 'updated_at'
    ];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'private_profile' => 'boolean'
    ];
    protected $appends  = [
        'averageSpeed'
    ];

    public function getAverageSpeedAttribute(): float {
        return $this->train_duration == 0 ? 0 : $this->train_distance / ($this->train_duration / 60);
    }

    public function socialProfile(): HasOne {
        return $this->hasOne(SocialLoginProfile::class);
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class);
    }

    public function home(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'home_id');
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class);
    }

    public function follows(): BelongsToMany {
        return $this->belongsToMany(User::class, 'follows', 'user_id', 'follow_id');
    }

    public function followers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'follows', 'user_id', 'user_id');
    }

    public function sessions(): HasMany {
        return $this->hasMany(Session::class);
    }

}
