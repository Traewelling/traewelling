<?php

namespace App\Models;

use App\Enum\MastodonVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int                      $id
 * @property int                      $user_id
 * @property int|null                 $twitter_id
 * @property int|null                 $mastodon_id
 * @property int|null                 $mastodon_server
 * @property mixed|null               $mastodon_token
 * @property MastodonVisibility       $mastodon_visibility
 * @property Carbon|null              $created_at
 * @property Carbon|null              $updated_at
 * @property-read MastodonServer|null $mastodonServer
 * @property-read User                $user
 * @method static Builder<static>|SocialLoginProfile newModelQuery()
 * @method static Builder<static>|SocialLoginProfile newQuery()
 * @method static Builder<static>|SocialLoginProfile query()
 * @method static Builder<static>|SocialLoginProfile whereCreatedAt($value)
 * @method static Builder<static>|SocialLoginProfile whereId($value)
 * @method static Builder<static>|SocialLoginProfile whereMastodonId($value)
 * @method static Builder<static>|SocialLoginProfile whereMastodonServer($value)
 * @method static Builder<static>|SocialLoginProfile whereMastodonToken($value)
 * @method static Builder<static>|SocialLoginProfile whereMastodonVisibility($value)
 * @method static Builder<static>|SocialLoginProfile whereTwitterId($value)
 * @method static Builder<static>|SocialLoginProfile whereUpdatedAt($value)
 * @method static Builder<static>|SocialLoginProfile whereUserId($value)
 * @mixin \Eloquent
 */
class SocialLoginProfile extends Model
{

    protected $fillable = [
        'user_id',
        'twitter_id',
        'mastodon_id', 'mastodon_server', 'mastodon_token', 'mastodon_visibility'
    ];
    protected $hidden   = ['mastodon_server', 'mastodon_token'];
    protected $casts    = [
        'id'                  => 'integer',
        'user_id'             => 'integer',
        'twitter_id'          => 'integer',
        'mastodon_id'         => 'integer',
        'mastodon_server'     => 'integer',
        'mastodon_visibility' => MastodonVisibility::class,
        'mastodon_token'      => 'encrypted',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mastodonServer(): BelongsTo {
        return $this->belongsTo(MastodonServer::class, 'mastodon_server', 'id');
    }
}
