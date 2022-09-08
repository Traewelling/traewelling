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

/**
 * @property int              user_id
 * @property string           body
 * @property Business         business
 * @property int              event_id
 * @property StatusVisibility visibility
 */
class Status extends Model
{

    use HasFactory;

    protected $fillable = ['body', 'user_id', 'business', 'visibility', 'type', 'event_id', 'effective_at'];
    protected $hidden   = ['user_id', 'business'];
    protected $appends  = ['favorited', 'socialText', 'statusInvisibleToMe', 'latitude', 'longitude'];
    protected $casts    = [
        'id'           => 'integer',
        'user_id'      => 'integer',
        'business'     => Business::class,
        'visibility'   => StatusVisibility::class,
        'event_id'     => 'integer',
        'effective_at' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany {
        return $this->hasMany(Like::class);
    }

    public function trainCheckin(): HasOne {
        return $this->hasOne(TrainCheckin::class, 'status_id', 'id');
    }

    public function locationCheckin(): HasOne {
        return $this->hasOne(LocationCheckin::class, 'status_id', 'id');
    }

    public function event(): HasOne {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function getFavoritedAttribute(): ?bool {
        if (!Auth::check()) {
            return null;
        }
        return $this->likes->contains('user_id', Auth::id());
    }

    public function getSocialTextAttribute(): string {
        if ($this->type === 'location') {
            return $this->locationCheckin->socialText;
        }
        if ($this->type === 'hafas') {
            return $this->trainCheckin->socialText;
        }
        return '';
    }

    /**
     * @deprecated ->   replaced by $user->can(...) / $user->cannot(...) /
     *                  request()->user()->can(...) / request()->user()->cannot(...)
     */
    public function getStatusInvisibleToMeAttribute(): bool {
        return !request()?->user()?->can('view', $this);
    }

    public function getLatitudeAttribute(): ?float {
        if ($this->type === 'location') {
            return $this->locationCheckin->location->latitude;
        }
        if ($this->type === 'hafas') {
            return $this->trainCheckin->Origin->latitude;
        }
        return null;
    }

    public function getLongitudeAttribute(): ?float {
        if ($this->type === 'location') {
            return $this->locationCheckin->location->longitude;
        }
        if ($this->type === 'hafas') {
            return $this->trainCheckin->Origin->longitude;
        }
        return null;
    }
}
