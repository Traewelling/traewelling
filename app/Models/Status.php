<?php

namespace App\Models;

use App\Enum\Business;
use App\Enum\StationIdentifierType;
use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @todo merge model with "Checkin" (later only "Checkin") because the difference between trip sources (HAFAS,
 *       User, and future sources) should be handled in the Trip model.
 *
 * @property int $id
 * @property string|null $body
 * @property int $user_id
 * @property Business|null $business
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $event_id
 * @property string|null $ticket_id
 * @property StatusVisibility $visibility
 * @property string|null $mastodon_post_id
 * @property int|null $client_id
 * @property string|null $moderation_notes
 * @property bool $lock_visibility
 * @property bool $hide_body
 * @property-read Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Checkin|null $checkin
 * @property-read Ticket|null $ticket
 * @property-read OAuthClient|null $client
 * @property-read User|null $createdByUser
 * @property-read Event|null $event
 * @property-read string $description
 * @property-read bool|null $favorited
 * @property-read bool $status_invisible_to_me
 * @property-read Collection<int, Like> $likes
 * @property-read int|null $likes_count
 * @property-read Collection<int, Mention> $mentions
 * @property-read int|null $mentions_count
 * @property-read Collection<int, StatusTag> $tags
 * @property-read int|null $tags_count
 * @property-read Checkin|null $trainCheckin
 * @property-read User|null $user
 *
 * @method static \Database\Factories\StatusFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereBusiness($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereHideBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereLockVisibility($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereMastodonPostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereModerationNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereVisibility($value)
 *
 * @property int|null $created_by_user_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereCreatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Status whereTicketId($value)
 *
 * @mixin \Eloquent
 */
class Status extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'created_by_user_id', 'body', 'business', 'visibility', 'event_id', 'ticket_id', 'mastodon_post_id', 'client_id',
        'moderation_notes', 'lock_visibility', 'hide_body',
    ];

    protected $hidden = ['user_id', 'created_by_user_id', 'business'];

    protected $appends = ['favorited', 'statusInvisibleToMe', 'description'];

    protected $casts = [
        'id' => 'integer',
        'body' => 'string',
        'user_id' => 'integer',
        'created_by_user_id' => 'integer',
        'business' => Business::class,
        'visibility' => StatusVisibility::class,
        'event_id' => 'integer',
        'ticket_id' => 'string',
        'mastodon_post_id' => 'string',
        'client_id' => 'integer',
        'moderation_notes' => 'string',
        'lock_visibility' => 'boolean',
        'hide_body' => 'boolean',
    ];

    protected static array $recordEvents = ['updated'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function checkin(): HasOne
    {
        return $this->hasOne(Checkin::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(OAuthClient::class, 'client_id', 'id');
    }

    /**
     * @deprecated use ->checkin instead
     */
    public function trainCheckin(): HasOne
    {
        return $this->checkin();
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(StatusTag::class, 'status_id', 'id');
    }

    public function mentions(): HasMany
    {
        return $this->hasMany(Mention::class, 'status_id', 'id');
    }

    public function getFavoritedAttribute(): ?bool
    {
        if (!Auth::check()) {
            return null;
        }

        return $this->likes->contains('user_id', Auth::id());
    }

    public function getDescriptionAttribute(): string
    {
        if ($this->checkin === null) {
            return $this->body ?? '';
        }

        $originStation = $this->checkin->originStopover->station;
        $destinationStation = $this->checkin->destinationStopover->station;
        $originRil = $originStation->getIdentifier(StationIdentifierType::DE_DB_RIL100)?->identifier;
        $destinationRil = $destinationStation->getIdentifier(StationIdentifierType::DE_DB_RIL100)?->identifier;

        return __('description.status', [
            'username' => $this->user->name,
            'origin' => $originStation->name . ($originRil ? ' (' . $originRil . ')' : ''),
            'destination' => $destinationStation->name . ($destinationRil ? ' (' . $destinationRil . ')' : ''),
            'date' => $this->checkin->departure->isoFormat(__('datetime-format')),
            'lineName' => $this->checkin->trip->linename,
        ]);
    }

    /**
     * @deprecated ->   replaced by $user->can(...) / $user->cannot(...) /
     *                  request()->user()->can(...) / request()->user()->cannot(...)
     */
    public function getStatusInvisibleToMeAttribute(): bool
    {
        return !request()?->user()?->can('view', $this);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['moderation_notes', 'lock_visibility', 'hide_body'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
