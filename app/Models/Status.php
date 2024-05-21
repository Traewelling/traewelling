<?php

namespace App\Models;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * //properties
 * @property int              id
 * @property int              user_id
 * @property string           body
 * @property Business         business
 * @property StatusVisibility visibility
 * @property int              event_id
 * @property string           tweet_id
 * @property string           mastodon_post_id
 *
 * //relations
 * @property User             $user
 * @property Checkin          $checkin
 * @property Collection       $likes
 * @property OAuthClient      $client
 * @property Event            $event
 * @property Collection       $tags
 * @property Mention[]        $mentions
 *
 * @todo merge model with "Checkin" (later only "Checkin") because the difference between trip sources (HAFAS,
 *       User, and future sources) should be handled in the Trip model.
 */
class Status extends Model
{

    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'body',
        'business',
        'visibility',
        'event_id',
        'tweet_id',
        'mastodon_post_id',
        'client_id'
    ];
    protected $hidden   = ['user_id', 'business'];
    protected $appends  = ['favorited', 'statusInvisibleToMe', 'description'];
    protected $casts    = [
        'id'               => 'integer',
        'user_id'          => 'integer',
        'business'         => Business::class,
        'visibility'       => StatusVisibility::class,
        'event_id'         => 'integer',
        'tweet_id'         => 'string',
        'mastodon_post_id' => 'string',
        'client_id'        => 'integer'
    ];

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
        return __('description.status', [
            'username'    => $this->user->name,
            'origin'      => $this->checkin->originStation->name .
                             ($this->checkin->originStation->rilIdentifier ?
                                 ' (' . $this->checkin->originStation->rilIdentifier . ')' : ''),
            'destination' => $this->checkin->destinationStation->name .
                             ($this->checkin->destinationStation->rilIdentifier ?
                                 ' (' . $this->checkin->destinationStation->rilIdentifier . ')' : ''),
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
                         ->dontSubmitEmptyLogs()
                         ->logOnlyDirty()
                         ->logFillable();
    }
}
