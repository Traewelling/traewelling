<?php

namespace App;

use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;

class SocialLoginProfile extends Model
{
    use Encryptable;

    protected $encryptable =  [
      'twitter_token',
      'twitter_tokenSecret',
      'mastodon_token'
    ];
}
