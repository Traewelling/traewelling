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
    protected       $casts       = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'twitter_id'      => 'integer',
        'mastodon_id'     => 'integer',
        'mastodon_server' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mastodonServer(): BelongsTo {
        return $this->belongsTo(MastodonServer::class, 'mastodon_server', 'id');
    }
}
