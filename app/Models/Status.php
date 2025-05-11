<?php

namespace App\Models;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use Database\Factories\StatusFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 
 *
 * @todo merge model with "Checkin" (later only "Checkin") because the difference between trip sources (HAFAS,
 *       User, and future sources) should be handled in the Trip model.
 * @property int                             $id
 * @property string|null                     $body
 * @property int                             $user_id
 * @property Business                        $business
 * @property StatusVisibility                $visibility
 * @property int|null                        $event_id
 * @property string|null                     $mastodon_post_id
 * @property int|null                        $client_id
 * @property string|null                     $moderation_notes Notes from the moderation team - visible to the user
 * @property bool                            $lock_visibility  Prevent the user from changing the visibility of the status?
 * @property bool                            $hide_body        Hide the body of the status from other users?
 * @property Carbon|null                     $created_at
 * @property Carbon|null                     $updated_at
 * @property-read Collection<int, Activity>  $activities
 * @property-read int|null                   $activities_count
 * @property-read Checkin|null               $checkin
 * @property-read OAuthClient|null           $client
 * @property-read Event|null                 $event
 * @property-read string                     $description
 * @property-read bool|null                  $favorited
 * @property-read bool                       $status_invisible_to_me
 * @property-read Collection<int, Like>      $likes
 * @property-read int|null                   $likes_count
 * @property-read Collection<int, Mention>   $mentions
 * @property-read int|null                   $mentions_count
 * @property-read Collection<int, StatusTag> $tags
 * @property-read int|null                   $tags_count
 * @property-read Checkin|null               $trainCheckin
 * @property-read User                       $user
 * @method static StatusFactory factory($count = null, $state = [])
 * @method static Builder<static>|Status newModelQuery()
 * @method static Builder<static>|Status newQuery()
 * @method static Builder<static>|Status query()
 * @method static Builder<static>|Status whereBody($value)
 * @method static Builder<static>|Status whereBusiness($value)
 * @method static Builder<static>|Status whereClientId($value)
 * @method static Builder<static>|Status whereCreatedAt($value)
 * @method static Builder<static>|Status whereEventId($value)
 * @method static Builder<static>|Status whereHideBody($value)
 * @method static Builder<static>|Status whereId($value)
 * @method static Builder<static>|Status whereLockVisibility($value)
 * @method static Builder<static>|Status whereMastodonPostId($value)
 * @method static Builder<static>|Status whereModerationNotes($value)
 * @method static Builder<static>|Status whereUpdatedAt($value)
 * @method static Builder<static>|Status whereUserId($value)
 * @method static Builder<static>|Status whereVisibility($value)
 * @mixin \Eloquent
 */
class Status extends Model
{

    use HasFactory, LogsActivity;

    protected              $fillable     = [
        'user_id', 'body', 'business', 'visibility', 'event_id', 'mastodon_post_id', 'client_id',
        'moderation_notes', 'lock_visibility', 'hide_body',
    ];
    protected              $hidden       = ['user_id', 'business'];
    protected              $appends      = ['favorited', 'statusInvisibleToMe', 'description'];
    protected              $casts        = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'business'         => Business::class,
        'visibility'       => StatusVisibility::class,
        'event_id'         => 'integer',
        'mastodon_post_id' => 'string',
        'client_id'        => 'integer',
        'moderation_notes' => 'string',
        'lock_visibility'  => 'boolean',
        'hide_body'        => 'boolean'
    ];
    protected static array $recordEvents = ['updated'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class);
    }

    public function checkin(): HasOne {
        return $this->hasOne(Checkin::class);
    }

    public function client(): BelongsTo {
        return $this->belongsTo(OAuthClient::class, 'client_id', 'id');
    }

    /**
     * @return HasOne
     * @deprecated use ->checkin instead
     */
    public function trainCheckin(): HasOne {
        return $this->checkin();
    }

    public function event(): HasOne {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function tags(): HasMany {
        return $this->hasMany(StatusTag::class, 'status_id', 'id');
    }

    public function mentions(): HasMany {
        return $this->hasMany(Mention::class, 'status_id', 'id');
    }

    public function getFavoritedAttribute(): ?bool {
        if (!Auth::check()) {
            return null;
        }
        return $this->likes->contains('user_id', Auth::id());
    }

    public function getDescriptionAttribute(): string {
        if ($this->checkin === null) {
            return $this->body ?? '';
        }
        return __('description.status', [
            'username'    => $this->user->name,
            'origin'      => $this->checkin->originStopover->station->name .
                             ($this->checkin->originStopover->station->rilIdentifier ?
                                 ' (' . $this->checkin->originStopover->station->rilIdentifier . ')' : ''),
            'destination' => $this->checkin->destinationStopover->station->name .
                             ($this->checkin->destinationStopover->station->rilIdentifier ?
                                 ' (' . $this->checkin->destinationStopover->station->rilIdentifier . ')' : ''),
            'date'        => $this->checkin->departure->isoFormat(__('datetime-format')),
            'lineName'    => $this->checkin->trip->linename
        ]);
    }

    /**
     * @deprecated ->   replaced by $user->can(...) / $user->cannot(...) /
     *                  request()->user()->can(...) / request()->user()->cannot(...)
     */
    public function getStatusInvisibleToMeAttribute(): bool {
        return !request()?->user()?->can('view', $this);
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->logOnly(['moderation_notes', 'lock_visibility', 'hide_body'])
                         ->logOnlyDirty()
                         ->dontSubmitEmptyLogs();
    }
}
