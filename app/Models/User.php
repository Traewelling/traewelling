<?php

namespace App\Models;

use App\Enum\StatusVisibility;
use App\Exceptions\RateLimitExceededException;
use App\Jobs\SendVerificationEmail;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Passport\HasApiTokens;
use Mastodon;

/**
 * @property int     id
 * @property string  username
 * @property string  name
 * @property string  avatar
 * @property string  email
 * @property Carbon  email_verified_at
 * @property string  password
 * @property int     home_id
 * @property Carbon  privacy_ack_at
 * @property integer default_status_visibility
 * @property boolean private_profile
 * @property boolean prevent_index
 * @property boolean likes_enabled
 * @property int     privacy_hide_days
 * @property string  language
 * @property Carbon  last_login
 */
class User extends Authenticatable implements MustVerifyEmail
{

    use Notifiable, HasApiTokens, HasFactory;

    protected $fillable = [
        'username', 'name', 'avatar', 'email', 'email_verified_at', 'password', 'home_id', 'privacy_ack_at',
        'default_status_visibility', 'likes_enabled', 'private_profile', 'prevent_index', 'privacy_hide_days',
        'language', 'last_login',
    ];
    protected $hidden   = [
        'password', 'remember_token', 'email', 'email_verified_at', 'privacy_ack_at',
        'home_id', 'avatar', 'role', 'social_profile', 'created_at', 'updated_at', 'userInvisibleToMe'
    ];
    protected $appends  = [
        'averageSpeed', 'points', 'userInvisibleToMe', 'twitterUrl', 'mastodonUrl', 'train_distance', 'train_duration',
        'following', 'followPending', 'muted'
    ];
    protected $casts    = [
        'id'                        => 'integer',
        'email_verified_at'         => 'datetime',
        'privacy_ack_at'            => 'datetime',
        'home_id'                   => 'integer',
        'private_profile'           => 'boolean',
        'likes_enabled'             => 'boolean',
        'default_status_visibility' => StatusVisibility::class,
        'prevent_index'             => 'boolean',
        'privacy_hide_days'         => 'integer',
        'role'                      => 'integer',
        'last_login'                => 'datetime',
    ];

    public function getTrainDistanceAttribute(): float {
        return TrainCheckin::whereIn('status_id', $this->statuses()->select('id'))
                           ->select('distance')
                           ->sum('distance');
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class);
    }

    public function trainCheckins(): HasMany {
        return $this->hasMany(TrainCheckin::class, 'user_id', 'id');
    }

    public function getTrainDurationAttribute(): float {
        return TrainCheckin::whereIn('status_id', $this->statuses()->select('id'))
                           ->select(['arrival', 'departure'])
                           ->get()
                           ->sum('duration');
    }

    /**
     * @return float
     * @deprecated Use speed variable at train_checkins instead
     */
    public function getAverageSpeedAttribute(): float {
        return $this->train_duration == 0 ? 0 : $this->train_distance / ($this->train_duration / 60);
    }

    public function socialProfile(): HasOne {
        if ($this->hasOne(SocialLoginProfile::class)->count() == 0) {
            SocialLoginProfile::create(['user_id' => $this->id]);
        }
        return $this->hasOne(SocialLoginProfile::class);
    }

    public function home(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'home_id');
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class);
    }

    public function follows(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'follows', 'user_id', 'follow_id');
    }

    public function blockedUsers(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'user_blocks', 'user_id', 'blocked_id');
    }

    public function blockedByUsers(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'user_blocks', 'blocked_id', 'user_id');
    }

    public function mutedUsers(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'user_mutes', 'user_id', 'muted_id');
    }

    public function followRequests(): HasMany {
        return $this->hasMany(FollowRequest::class, 'follow_id', 'id');
    }

    /**
     * @deprecated
     */
    public function followers(): HasMany {
        return $this->hasMany(Follow::class, 'follow_id', 'id');
    }

    /**
     * @deprecated
     */
    public function followings(): HasMany {
        return $this->hasMany(Follow::class, 'user_id', 'id');
    }

    public function sessions(): HasMany {
        return $this->hasMany(Session::class);
    }

    public function icsTokens(): HasMany {
        return $this->hasMany(IcsToken::class, 'user_id', 'id');
    }

    public function webhooks(): HasMany {
        return $this->hasMany(Webhook::class);
    }

    public function getPointsAttribute(): int {
        return TrainCheckin::whereIn('status_id', $this->statuses()->select('id'))
                           ->where('departure', '>=', Carbon::now()->subDays(7)->toIso8601String())
                           ->select('points')
                           ->sum('points');
    }

    /**
     * @untested
     * @todo test
     */
    public function userFollowings(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'follows', 'user_id', 'follow_id');
    }

    /**
     * @untested
     * @todo test
     */
    public function userFollowers(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'follows', 'follow_id', 'user_id');
    }

    /**
     * @untested
     * @todo test
     */
    public function userFollowRequests(): BelongsToMany {
        return $this->belongsToMany(__CLASS__, 'follow_requests', 'follow_id', 'user_id');
    }

    /**
     * @deprecated -> replaced by $user->can(...) / $user->cannot(...) / request()->user()->can(...) /
     *             request()->user()->cannot(...)
     */
    public function getUserInvisibleToMeAttribute(): bool {
        return !request()?->user()?->can('view', $this);
    }

    public function getFollowingAttribute(): bool {
        return (auth()->check() && $this->followers->contains('user_id', auth()->user()->id));
    }

    public function getFollowPendingAttribute(): bool {
        return (auth()->check() && $this->followRequests->contains('user_id', auth()->user()->id));
    }

    public function getMutedAttribute(): bool {
        return (auth()->check() && auth()->user()->mutedUsers->contains('id', $this->id));
    }

    /**
     * The auth-user is blocked by $this user. auth-user can not see $this's statuses.
     */
    public function getIsAuthUserBlockedAttribute(): bool {
        return (auth()->check() && $this->blockedUsers->contains('id', auth()->user()->id));
    }

    /**
     * The auth-user has blocked $this user. $this can not see auth-user's statuses.
     */
    public function getIsBlockedByAuthUserAttribute(): bool {
        return (auth()->check() && $this->blockedByUsers->contains('id', auth()->user()->id));
    }

    /**
     * @return string|null
     * @deprecated
     */
    public function getTwitterUrlAttribute(): ?string {
        return null; //Twitter isn't used by traewelling anymore
    }

    public function getMastodonUrlAttribute(): ?string {
        $mastodonUrl = null;
        if (!empty($this->socialProfile)
            && !empty($this->socialProfile->mastodon_token)
            && !empty($this->socialProfile->mastodon_id)) {
            try {
                $mastodonServer = MastodonServer::where('id', $this->socialProfile->mastodon_server)->first();
                if ($mastodonServer) {
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

    /**
     * Get the entity's notifications.
     *
     * @return MorphMany
     */
    public function notifications(): MorphMany {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    /**
     * @throws RateLimitExceededException
     */
    public function sendEmailVerificationNotification(): void {
        Log::info(sprintf("Attempting to send verification email for user#%s w/ mail %s", $this->id, $this->email));

        $executed = RateLimiter::attempt(
            key:          'verification-mail-sent-' . $this->email,
            maxAttempts:  1,
            callback: function() {
                SendVerificationEmail::dispatch($this);
                Log::info(sprintf(
                              "Sent the verification email for user#%s w/ mail %s successfully.",
                              $this->id,
                              $this->email
                          ));
            },
            decaySeconds: 5 * 60,
        );

        if (!$executed) {
            Log::info(sprintf(
                          "Sending the verification email for user#%s w/mail %s was rate-limited.",
                          $this->id,
                          $this->email
                      ));
            throw new RateLimitExceededException();
        }
    }
}
