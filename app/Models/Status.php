<?php

namespace App\Models;

use App\Enum\Business;
use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @todo merge model with "Checkin" (later only "Checkin") because the difference between trip sources (HAFAS,
 *       User, and future sources) should be handled in the Trip model.
 */
class Status extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id', 'body', 'business', 'visibility', 'event_id', 'mastodon_post_id', 'client_id',
        'moderation_notes', 'lock_visibility', 'hide_body',
    ];

    protected $hidden = ['user_id', 'business'];

    protected $appends = ['favorited', 'statusInvisibleToMe', 'description'];

    protected $casts = [
        'id' => 'integer',
        'body' => 'string',
        'user_id' => 'integer',
        'business' => Business::class,
        'visibility' => StatusVisibility::class,
        'event_id' => 'integer',
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

        return __('description.status', [
            'username' => $this->user->name,
            'origin' => $this->checkin->originStopover->station->name .
                             ($this->checkin->originStopover->station->rilIdentifier ?
                                 ' (' . $this->checkin->originStopover->station->rilIdentifier . ')' : ''),
            'destination' => $this->checkin->destinationStopover->station->name .
                             ($this->checkin->destinationStopover->station->rilIdentifier ?
                                 ' (' . $this->checkin->destinationStopover->station->rilIdentifier . ')' : ''),
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
            ->dontSubmitEmptyLogs();
    }
}
