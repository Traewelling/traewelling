<?php

namespace App\Models;

use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLoginProfile extends Model
{

    use Encryptable;

    protected       $fillable    = [
        'user_id',
        'twitter_id', 'twitter_token', 'twitter_tokenSecret',
        'mastodon_id', 'mastodon_server', 'mastodon_token'
    ];
    protected       $hidden      = ['twitter_token', 'twitter_tokenSecret', 'mastodon_server', 'mastodon_token'];
    protected array $encryptable = [
        'twitter_token', 'twitter_tokenSecret', 'mastodon_token'
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
