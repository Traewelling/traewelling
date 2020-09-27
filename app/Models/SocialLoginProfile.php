<?php

namespace App\Models;

use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;

class SocialLoginProfile extends Model {

    use Encryptable;

    protected $fillable = [
        'user_id',
        'twitter_id',
        'mastodon_id',
        'mastodon_server'
    ];

    protected $encryptable = [
        'twitter_token',
        'twitter_tokenSecret',
        'mastodon_token'
    ];
}
