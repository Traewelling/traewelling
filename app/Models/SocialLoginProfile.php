<?php

namespace App\Models;

use App\Enum\MastodonVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLoginProfile extends Model
{

    protected $fillable = [
        'user_id',
        'twitter_id', 'twitter_token', 'twitter_tokenSecret', 'twitter_refresh_token', 'twitter_token_expires_at',
        'mastodon_id', 'mastodon_server', 'mastodon_token', 'mastodon_visibility'
    ];
    protected $hidden   = ['twitter_token', 'twitter_tokenSecret', 'twitter_refresh_token', 'mastodon_server', 'mastodon_token'];
    protected $casts    = [
        'id'                       => 'integer',
        'user_id'                  => 'integer',
        'twitter_id'               => 'integer',
        'mastodon_id'              => 'integer',
        'mastodon_server'          => 'integer',
        'mastodon_visibility'      => MastodonVisibility::class,
        'twitter_token'            => 'encrypted',
        'twitter_tokenSecret'      => 'encrypted',
        'twitter_refresh_token'    => 'encrypted',
        'twitter_token_expires_at' => 'datetime',
        'mastodon_token'           => 'encrypted',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mastodonServer(): BelongsTo {
        return $this->belongsTo(MastodonServer::class, 'mastodon_server', 'id');
    }
}
