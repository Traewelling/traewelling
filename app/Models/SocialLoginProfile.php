<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLoginProfile extends Model
{

    protected $fillable = [
        'user_id',
        'twitter_id', 'twitter_token', 'twitter_tokenSecret',
        'mastodon_id', 'mastodon_server', 'mastodon_token'
    ];
    protected $hidden   = ['twitter_token', 'twitter_tokenSecret', 'mastodon_server', 'mastodon_token'];
    protected $casts    = [
        'twitter_token'       => 'encrypted',
        'twitter_tokenSecret' => 'encrypted',
        'mastodon_token'      => 'encrypted',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
