<?php

namespace App\Models;

use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;
use Mastodon;

class User extends Authenticatable implements MustVerifyEmail
{

    use Notifiable, HasApiTokens, HasFactory;

    protected $fillable = [
        'username', 'name', 'avatar', 'email', 'password', 'home_id',
        'always_dbl', 'private_profile', 'prevent_index', 'language'
    ];
    protected $hidden   = [
        'password', 'remember_token', 'email', 'email_verified_at', 'privacy_ack_at',
        'home_id', 'avatar', 'always_dbl', 'role', 'social_profile', 'created_at', 'updated_at', 'userInvisibleToMe'
    ];
    protected $casts    = [
        'email_verified_at' => 'datetime',
        'private_profile'   => 'boolean',
        'prevent_index'     => 'boolean',
    ];
    protected $appends  = [
        'averageSpeed', 'points', 'userInvisibleToMe', 'twitterUrl', 'mastodonUrl'
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

    public function mutedUsers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'user_mutes', 'user_id', 'muted_id');
    }

    public function followRequests(): HasMany {
        return $this->hasMany(FollowRequest::class, 'follow_id', 'id');
    }

    public function followers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'follows', 'follow_id', 'user_id');
    }

    public function sessions(): HasMany {
        return $this->hasMany(Session::class);
    }

    public function icsTokens(): HasMany {
        return $this->hasMany(IcsToken::class, 'user_id', 'id');
    }

    public function getPointsAttribute(): int {
        return TrainCheckin::whereIn('status_id', $this->statuses()->select('id'))
                           ->where('departure', '>=', Carbon::now()->subDays(7)->toIso8601String())
                           ->select('points')
                           ->sum('points');
    }

    /**
     * Checks if this user is invisible to "me".
     * +---------+---------------+-----------+--------+
     * | Private | authenticated | following | result |
     * +---------+---------------+-----------+--------+
     * |       0 |             0 |         0 | 0      |
     * |       0 |             0 |         1 | 0      |
     * |       0 |             1 |         0 | 0      |
     * |       0 |             1 |         1 | 0      |
     * |       1 |             0 |         0 | 1      |
     * |       1 |             0 |         1 | ?      |
     * |       1 |             1 |         0 | 1      |
     * |       1 |             1 |         1 | 0      |
     * +---------+---------------+-----------+--------+
     *
     * @return bool
     */
    public function getUserInvisibleToMeAttribute(): bool {
        if (auth()->check()
            && $this->id != auth()->user()->id
            && auth()->user()->mutedUsers->contains('id', $this->id)) {
            return true;
        }
        return $this->private_profile
               && (!Auth::check()
                   || (Auth::check()
                       && ($this->id != Auth::id() && !Auth::user()->follows->contains('id', $this->id))
                   )
               );
    }

    public function getTwitterUrlAttribute(): ?string {
        $twitterUrl = null;
        if ($this->socialProfile != null
            && !empty($this->socialProfile->twitter_token)
            && !empty($this->socialProfile->twitter_tokenSecret)) {
            try {
                $connection = new TwitterOAuth(
                    config('trwl.twitter_id'),
                    config('trwl.twitter_secret'),
                    $this->socialProfile->twitter_token,
                    $this->socialProfile->twitter_tokenSecret
                );

                $getInfo    = $connection->get('users/show', ['user_id' => $this->socialProfile->twitter_id]);
                $twitterUrl = "https://twitter.com/" . $getInfo->screen_name;
            } catch (Exception $exception) {
                // The big whale time or $user has removed the api rights but has not told us yet.
                Log::warning($exception);
            }
        }
        return $twitterUrl;
    }

    public function getMastodonUrlAttribute(): ?string {
        $mastodonUrl = null;
        if ($this->socialProfile != null) {
            try {
                $mastodonServer = MastodonServer::where('id', $this->socialProfile->mastodon_server)->first();
                if ($mastodonServer != null) {
                    $mastodonDomain      = $mastodonServer->domain;
                    $mastodonAccountInfo = Mastodon::domain($mastodonDomain)
                                                   ->token($this->socialProfile->mastodon_token)
                                                   ->get("/accounts/" . $this->socialProfile->mastodon_id);
                    $mastodonUrl         = $mastodonAccountInfo["url"];
                }
            } catch (Exception $exception) {
                // The connection might be broken, or the instance is down, or $user has removed the api rights
                // but has not told us yet.
                Log::warning($exception);
            }
        }
        return $mastodonUrl;
    }
}
