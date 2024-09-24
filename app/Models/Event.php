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
 * // properties
 * @property int     id
 * @property string  name
 * @property ?string hashtag
 * @property int     station_id
 * @property string  slug
 * @property string  host
 * @property string  url
 * @property Carbon  checkin_start Timestamp from when checkins are allowed
 * @property Carbon  checkin_end   Timestamp until when checkins are allowed
 * @property Carbon  event_start   Timestamp when the event starts (if different from checkin_start)
 * @property Carbon  event_end     Timestamp when the event ends (if different from checkin_end)
 *
 * // appends
 * @property int     totalDistance
 * @property int     totalDuration
 * @property bool    isPride
 * @property Carbon  start         Timestamp of event starts (returns event_start or checkin_start)
 * @property Carbon  end           Timestamp of event ends (returns event_end or checkin_end)
 * @property bool    hasExtendedCheckin
 */
class Event extends Model
{

    use HasFactory, LogsActivity;

    protected $fillable = [
        'name', 'hashtag', 'station_id', 'slug', 'host', 'url',
        'checkin_start', 'checkin_end',
        'event_start', 'event_end'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['totalDistance', 'totalDuration', 'isPride'];
    protected $casts    = [
        'id'            => 'integer',
        'station_id'    => 'integer',
        'checkin_start' => 'datetime',
        'checkin_end'   => 'datetime',
        'event_start'   => 'datetime',
        'event_end'     => 'datetime',
    ];

    public function station(): HasOne {
        return $this->hasOne(Station::class, 'id', 'station_id');
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class);
    }

    public function getTotalDistanceAttribute(): int {
        return Cache::remember('event_' . $this->id . '_total_distance', now()->addMinutes(30), function() {
            return Checkin::whereIn('status_id', $this->statuses()->select('id'))
                          ->sum('distance');
        });
    }

    public function getTotalDurationAttribute(): int {
        return Cache::remember('event_' . $this->id . '_total_duration', now()->addMinutes(30), function() {
            return Checkin::whereIn('status_id', $this->statuses()->select('id'))
                          ->sum('duration');
        });
    }

    public function getIsPrideAttribute(): bool {
        $eventNameLowercase = strtolower($this->name);
        return Str::contains($eventNameLowercase, ['csd', 'pride']);
    }

    public function getStartAttribute(): Carbon {
        return $this->event_start ? $this->event_start : $this->checkin_start;
    }

    public function getEndAttribute(): Carbon {
        return $this->event_end ? $this->event_end : $this->checkin_end;
    }

    public function getHasExtendedCheckinAttribute(): bool {
        return ($this->event_start && $this->event_start != $this->checkin_start)
            || ($this->event_end && $this->event_end != $this->checkin_end);
    }

    public function approvedBy(): HasOne {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->logOnlyDirty()
                         ->logFillable();
    }

    /**
     * @param string $slug the slug of the event
     *
     * @return Event|null returns the event with the given slug or null if it does not exist
     */
    public static function getBySlug(string $slug): ?Event {
        return self::where('slug', '=', $slug)->firstOrFail();
    }

    /**
     * Returns a query for events that are active (or upcoming) at the given timestamp.
     *
     * @param Carbon $timestamp
     * @param bool   $showUpcoming
     *
     * @return Builder query for events that are active (or upcoming) at the given timestamp
     */
    public static function forTimestamp(Carbon $timestamp, bool $showUpcoming = false): Builder {
        $query = self::where('checkin_end', '>=', $timestamp)
                     ->orderBy('checkin_start', 'asc');
        if (!$showUpcoming) {
            $query->where('checkin_start', '<=', $timestamp);
        }
        return $query;
    }
}
