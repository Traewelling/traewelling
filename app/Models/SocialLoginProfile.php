<?php

namespace App\Models;

use App\Enum\MastodonVisibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLoginProfile extends Model
{

    protected $fillable = [
        'user_id',
        'twitter_id',
        'mastodon_id', 'mastodon_server', 'mastodon_token', 'mastodon_visibility'
    ];
    protected $hidden   = ['mastodon_server', 'mastodon_token'];
    protected $casts    = [
        'id'                       => 'integer',
        'user_id'                  => 'integer',
        'twitter_id'               => 'integer',
        'mastodon_id'              => 'integer',
        'mastodon_server'          => 'integer',
        'mastodon_visibility'      => MastodonVisibility::class,
        'mastodon_token'           => 'encrypted',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mastodonServer(): BelongsTo {
        return $this->belongsTo(MastodonServer::class, 'mastodon_server', 'id');
    }
}
