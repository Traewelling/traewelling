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
 * @property Carbon  begin // @todo rename to "checkin_start"?
 * @property Carbon  end   // @todo rename to "checkin_end"?
 * @property Carbon  event_start
 * @property Carbon  event_end
 *
 * // appends
 * @property int     totalDistance
 * @property int     totalDuration
 * @property bool    isPride
 */
class Event extends Model
{

    use HasFactory, LogsActivity;

    protected $fillable = [
        'name', 'hashtag', 'station_id', 'slug', 'host', 'url', 'begin', 'end', 'event_start', 'event_end'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['totalDistance', 'totalDuration', 'isPride'];
    protected $casts    = [
        'id'          => 'integer',
        'station_id'  => 'integer',
        'begin'       => 'datetime',
        'end'         => 'datetime',
        'event_start' => 'datetime',
        'event_end'   => 'datetime',
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

    public function approvedBy(): HasOne {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->logOnlyDirty()
                         ->logOnly([
                                       'name', 'hashtag', 'station_id', 'slug', 'host',
                                       'url', 'begin', 'end', 'event_start', 'event_end'
                                   ]);
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
        $query = self::where('end', '>=', $timestamp)
                     ->orderBy('begin', 'asc');
        if (!$showUpcoming) {
            $query->where('begin', '<=', $timestamp);
        }
        return $query;
    }
}
