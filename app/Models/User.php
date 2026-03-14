<?php

namespace App\Models;

use App\Enum\DataProvider;
use App\Enum\MapProvider;
use App\Enum\StatusVisibility;
use App\Enum\User\FriendCheckinSetting;
use App\Exceptions\RateLimitExceededException;
use App\Http\Controllers\Backend\SettingsController;
use App\Http\Controllers\Backend\Social\MastodonProfileDetails;
use App\Services\PersonalDataSelection\UserGdprDataService;
use Carbon\Carbon;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;

/**
 * @todo rename home_id to home_station_id
 * @todo rename mapprovider to map_provider
 *
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string|null $avatar
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $privacy_ack_at
 * @property string|null $password
 * @property int|null $home_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $private_profile
 * @property bool $prevent_index
 * @property string|null $language
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property StatusVisibility $default_status_visibility
 * @property int|null $privacy_hide_days
 * @property bool $likes_enabled
 * @property MapProvider $mapprovider
 * @property string $timezone
 * @property FriendCheckinSetting $friend_checkin
 * @property bool $points_enabled
 * @property \Illuminate\Support\Carbon|null $recent_gdpr_export
 * @property DataProvider $data_provider
 * @property string|null $bio
 * @property-read Collection<int, User> $blockedByUsers
 * @property-read int|null $blocked_by_users_count
 * @property-read Collection<int, User> $blockedUsers
 * @property-read int|null $blocked_users_count
 * @property-read Collection<int, OAuthClient> $clients
 * @property-read int|null $clients_count
 * @property-read Collection<int, FollowRequest> $followRequests
 * @property-read int|null $follow_requests_count
 * @property-read Collection<int, Follow> $followers
 * @property-read int|null $followers_count
 * @property-read Collection<int, Follow> $followings
 * @property-read int|null $followings_count
 * @property-read Collection<int, User> $follows
 * @property-read int|null $follows_count
 * @property-read bool $follow_pending
 * @property-read bool $followed_by
 * @property-read bool $following
 * @property-read bool $is_auth_user_blocked
 * @property-read bool $is_blocked_by_auth_user
 * @property-read string|null $mastodon_url
 * @property-read bool $muted
 * @property-read int $points
 * @property-read float $train_distance
 * @property-read float $train_duration
 * @property-read bool $user_invisible_to_me
 * @property-read Station|null $home
 * @property-read Collection<int, IcsToken> $icsTokens
 * @property-read int|null $ics_tokens_count
 * @property-read Collection<int, Like> $likes
 * @property-read int|null $likes_count
 * @property-read Collection<int, User> $mutedUsers
 * @property-read int|null $muted_users_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, OAuthClient> $oAuthClients
 * @property-read int|null $o_auth_clients_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, ProfileLink> $profileLinks
 * @property-read int|null $profile_links_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, Session> $sessions
 * @property-read int|null $sessions_count
 * @property-read SocialLoginProfile|null $socialProfile
 * @property-read Collection<int, Status> $statuses
 * @property-read int|null $statuses_count
 * @property-read Collection<int, Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, Checkin> $trainCheckins
 * @property-read int|null $train_checkins_count
 * @property-read Collection<int, TrustedUser> $trustedByUsers
 * @property-read int|null $trusted_by_users_count
 * @property-read Collection<int, TrustedUser> $trustedUsers
 * @property-read int|null $trusted_users_count
 * @property-read Collection<int, User> $userFollowRequests
 * @property-read int|null $user_follow_requests_count
 * @property-read Collection<int, User> $userFollowers
 * @property-read int|null $user_followers_count
 * @property-read Collection<int, User> $userFollowings
 * @property-read int|null $user_followings_count
 * @property-read Collection<int, Webhook> $webhooks
 * @property-read int|null $webhooks_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDataProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDefaultStatusVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFriendCheckin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHomeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLikesEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMapprovider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePointsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePreventIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrivacyAckAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrivacyHideDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePrivateProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRecentGdprExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 *
 * @property int $contribution_xp
 * @property int $contribution_level
 * @property-read Collection<int, MailChange> $mailChanges
 * @property-read int|null $mail_changes_count
 * @property-read Collection<int, OAuthClient> $oauthApps
 * @property-read int|null $oauth_apps_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereContributionLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereContributionXp($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements ExportsPersonalData, OAuthenticatable
{
    use HasApiTokens, HasFactory, HasPermissions, HasRoles, MustVerifyEmail, Notifiable;

    protected static function boot(): void
    {
        parent::boot();

        // UUID is not yet the primary key, so we can't use the HasUuids trait here.
        // Once the migration to UUID as primary key is complete, this can be replaced with the trait.
        static::creating(function (self $user): void {
            if (empty($user->uuid)) {
                $user->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'uuid',
        'username', 'name', 'avatar', 'email', 'email_verified_at', 'password', 'home_id', 'privacy_ack_at',
        'default_status_visibility', 'likes_enabled', 'points_enabled', 'private_profile', 'prevent_index',
        'privacy_hide_days', 'language', 'last_login', 'mapprovider', 'timezone', 'friend_checkin', 'data_provider', 'recent_gdpr_export',
        'bio', 'contribution_xp', 'contribution_level',
    ];

    protected $hidden = [
        'password', 'remember_token', 'email', 'email_verified_at', 'privacy_ack_at',
        'home_id', 'avatar', 'social_profile', 'created_at', 'updated_at', 'userInvisibleToMe',
    ];

    protected $appends = [
        'points', 'userInvisibleToMe', 'mastodonUrl', 'train_distance', 'train_duration',
        'following', 'followPending', 'muted', 'isAuthUserBlocked', 'isBlockedByAuthUser',
    ];

    protected $casts = [
        'id' => 'integer',
        'uuid' => 'string',
        'email_verified_at' => 'datetime',
        'privacy_ack_at' => 'datetime',
        'home_id' => 'integer',
        'private_profile' => 'boolean',
        'likes_enabled' => 'boolean',
        'points_enabled' => 'boolean',
        'default_status_visibility' => StatusVisibility::class,
        'prevent_index' => 'boolean',
        'privacy_hide_days' => 'integer',
        'last_login' => 'datetime',
        'mapprovider' => MapProvider::class,
        'data_provider' => DataProvider::class,
        'timezone' => 'string',
        'friend_checkin' => FriendCheckinSetting::class,
        'recent_gdpr_export' => 'datetime',
        'contribution_xp' => 'integer',
        'contribution_level' => 'integer',
    ];

    public function getTrainDistanceAttribute(): float
    {
        return (float) $this->getDistanceDurationSums()->distance;
    }

    public function trainCheckins(): HasMany
    {
        return $this->hasMany(Checkin::class, 'user_id', 'id');
    }

    /**
     * Since duration is a cached and calculated value, it can happen that some checkins are not included in the sum.
     */
    public function getTrainDurationAttribute(): float
    {
        return (float) $this->getDistanceDurationSums()->duration;
    }

    private function getDistanceDurationSums(): object
    {
        return $this->distanceDurationSums ??= Checkin::where('user_id', $this->id)
            ->selectRaw('SUM(distance) as distance, SUM(duration) as duration')
            ->first();
    }

    public function socialProfile(): HasOne
    {
        if ($this->hasOne(SocialLoginProfile::class)->count() == 0) {
            SocialLoginProfile::create(['user_id' => $this->id]);
        }

        return $this->hasOne(SocialLoginProfile::class);
    }

    public function home(): HasOne
    {
        return $this->hasOne(Station::class, 'id', 'home_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function follows(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'user_id', 'follow_id');
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'user_blocks', 'user_id', 'blocked_id');
    }

    public function blockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'user_blocks', 'blocked_id', 'user_id');
    }

    public function mutedUsers(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'user_mutes', 'user_id', 'muted_id');
    }

    public function followRequests(): HasMany
    {
        return $this->hasMany(FollowRequest::class, 'follow_id', 'id');
    }

    /**
     * @deprecated use ->userFollowers instead to get the users directly
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follow::class, 'follow_id', 'id');
    }

    /**
     * @deprecated use ->userFollowing instead to get the users directly
     */
    public function followings(): HasMany
    {
        return $this->hasMany(Follow::class, 'user_id', 'id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function icsTokens(): HasMany
    {
        return $this->hasMany(IcsToken::class, 'user_id', 'id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function getPointsAttribute(): int
    {
        return Checkin::where('user_id', $this->id)
            ->where('departure', '>=', Carbon::now()->subDays(7)->format('Y-m-d H:i:s'))
            ->sum('points');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function trustedUsers(): HasMany
    {
        return $this->hasMany(TrustedUser::class, 'user_id', 'id')
            ->with(['trusted'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function trustedByUsers(): HasMany
    {
        return $this->hasMany(TrustedUser::class, 'trusted_id', 'id')
            ->with(['user'])
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function userFollowings(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'user_id', 'follow_id');
    }

    public function userFollowers(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'follow_id', 'user_id');
    }

    /**
     * @untested
     *
     * @todo test
     */
    public function userFollowRequests(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'follow_requests', 'follow_id', 'user_id');
    }

    /**
     * @deprecated -> replaced by $user->can(...) / $user->cannot(...) / request()->user()->can(...) /
     *             request()->user()->cannot(...)
     */
    public function getUserInvisibleToMeAttribute(): bool
    {
        return !request()?->user()?->can('view', $this);
    }

    public function getFollowingAttribute(): bool
    {
        return auth()->check() && $this->followers->contains('user_id', auth()->user()->id);
    }

    public function getFollowPendingAttribute(): bool
    {
        return auth()->check() && $this->followRequests->contains('user_id', auth()->user()->id);
    }

    public function getMutedAttribute(): bool
    {
        return auth()->check() && auth()->user()->mutedUsers->contains('id', $this->id);
    }

    public function getFollowedByAttribute(): bool
    {
        return auth()->check() && $this->followings->contains('follow_id', auth()->user()->id);
    }

    /**
     * The auth-user is blocked by $this user. auth-user can not see $this's statuses.
     */
    public function getIsAuthUserBlockedAttribute(): bool
    {
        return auth()->check() && $this->blockedUsers->contains('id', auth()->user()->id);
    }

    /**
     * The auth-user has blocked $this user. $this can not see auth-user's statuses.
     */
    public function getIsBlockedByAuthUserAttribute(): bool
    {
        return auth()->check() && $this->blockedByUsers->contains('id', auth()->user()->id);
    }

    public function getMastodonUrlAttribute(): ?string
    {
        return (new MastodonProfileDetails($this))->getProfileUrl();
    }

    /**
     * Get the entity's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    /**
     * @throws RateLimitExceededException
     */
    public function sendEmailVerificationNotification(): void
    {
        SettingsController::sendEmailVerificationNotification($this);
    }

    /**
     * Laravel default function (e.g. for notifications)
     */
    public function preferredLocale(): string
    {
        return $this->language;
    }

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }

    public function oAuthClients(): HasMany
    {
        return $this->hasMany(OAuthClient::class, 'user_id', 'id');
    }

    public function selectPersonalData(PersonalDataSelection $personalDataSelection): void
    {
        (new UserGdprDataService())->addUserPersonalData($personalDataSelection, $this);
    }

    public function personalDataExportName(): string
    {
        return $this->username;
    }

    public function profileLinks(): HasMany
    {
        return $this->hasMany(ProfileLink::class, 'user_id', 'id');
    }

    public function mailChanges(): HasMany
    {
        return $this->hasMany(MailChange::class, 'user_id', 'id');
    }
}
