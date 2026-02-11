<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property int|null $station_id
 * @property string $name
 * @property string $slug
 * @property string|null $hashtag
 * @property string|null $host
 * @property string|null $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $event_start
 * @property \Illuminate\Support\Carbon|null $event_end
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon $checkin_start
 * @property \Illuminate\Support\Carbon $checkin_end
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\User|null $approvedBy
 * @property-read Carbon $end
 * @property-read bool $has_extended_checkin
 * @property-read bool $is_pride
 * @property-read Carbon $start
 * @property-read int $total_distance
 * @property-read int $total_duration
 * @property-read \App\Models\Station|null $station
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Status> $statuses
 * @property-read int|null $statuses_count
 *
 * @method static \Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCheckinEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCheckinStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHashtag($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUrl($value)
 *
 * @mixin \Eloquent
 */
class Event extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name', 'hashtag', 'station_id', 'slug', 'host', 'url',
        'checkin_start', 'checkin_end',
        'event_start', 'event_end',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $appends = ['totalDistance', 'totalDuration', 'isPride'];

    protected $casts = [
        'id' => 'integer',
        'station_id' => 'integer',
        'checkin_start' => 'datetime',
        'checkin_end' => 'datetime',
        'event_start' => 'datetime',
        'event_end' => 'datetime',
    ];

    public function station(): HasOne
    {
        return $this->hasOne(Station::class, 'id', 'station_id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class);
    }

    public function getTotalDistanceAttribute(): int
    {
        return Cache::remember('event_' . $this->id . '_total_distance', now()->addMinutes(30), function () {
            return Checkin::whereIn('status_id', $this->statuses()->select('id'))
                ->sum('distance');
        });
    }

    public function getTotalDurationAttribute(): int
    {
        return Cache::remember('event_' . $this->id . '_total_duration', now()->addMinutes(30), function () {
            return Checkin::whereIn('status_id', $this->statuses()->select('id'))
                ->sum('duration');
        });
    }

    public function getIsPrideAttribute(): bool
    {
        $eventNameLowercase = strtolower($this->name);

        return Str::contains($eventNameLowercase, ['csd', 'pride']);
    }

    public function getStartAttribute(): Carbon
    {
        return $this->event_start ? $this->event_start : $this->checkin_start;
    }

    public function getEndAttribute(): Carbon
    {
        return $this->event_end ? $this->event_end : $this->checkin_end;
    }

    public function getHasExtendedCheckinAttribute(): bool
    {
        return ($this->event_start && $this->event_start != $this->checkin_start)
               || ($this->event_end && $this->event_end != $this->checkin_end);
    }

    public function approvedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logFillable();
    }

    /**
     * @param  string  $slug  the slug of the event
     * @return Event|null returns the event with the given slug or null if it does not exist
     */
    public static function getBySlug(string $slug): ?Event
    {
        return self::where('slug', '=', $slug)->firstOrFail();
    }

    /**
     * Returns a query for events that are active (or upcoming) at the given timestamp.
     *
     *
     * @return Builder query for events that are active (or upcoming) at the given timestamp
     */
    public static function forTimestamp(Carbon $timestamp, bool $showUpcoming = false): Builder
    {
        $query = self::where('checkin_end', '>=', $timestamp)
            ->orderBy('checkin_start', 'asc');
        if (!$showUpcoming) {
            $query->where('checkin_start', '<=', $timestamp);
        }

        return $query;
    }
}
