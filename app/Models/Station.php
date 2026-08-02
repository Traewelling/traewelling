<?php

namespace App\Models;

use App\Enum\StationIdentifierType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use phpGPX\Models\Point;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @todo rename table to "Station" (without Train - we have more than just trains)
 *
 * @property int $id
 * @property string|null $uuid
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $time_offset
 * @property bool|null $shift_time
 * @property string|null $source
 * @property int $relevance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read AreasStationsMap|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Area> $areas
 * @property-read int|null $areas_count
 * @property-read Point $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StationIdentifier> $stationIdentifiers
 * @property-read int|null $station_identifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Stopover> $stopovers
 * @property-read int|null $stopovers_count
 *
 * @method static \Database\Factories\StationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereRelevance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereShiftTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereTimeOffset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Station whereUuid($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @mixin \Eloquent
 */
class Station extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'train_stations';

    protected static function boot(): void
    {
        parent::boot();

        // UUID is not yet the primary key, so we can't use the HasUuids trait here.
        static::creating(function (self $station): void {
            if (empty($station->uuid)) {
                $station->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'relevance',
        'name', 'latitude', 'longitude', 'source', 'time_offset', 'shift_time',
    ];

    protected $hidden = ['created_at', 'updated_at', 'time_offset', 'shift_time'];

    protected $casts = [
        'id' => 'integer',
        'uuid' => 'string',
        'name' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = ['location'];

    public function getLocationAttribute(): Point
    {
        $point = new Point(Point::TRACKPOINT);
        $point->latitude = $this->latitude;
        $point->longitude = $this->longitude;

        return $point;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->dontLogEmptyChanges()
            ->logOnlyDirty();
    }

    public function stationIdentifiers(): HasMany
    {
        return $this->hasMany(StationIdentifier::class, 'station_id', 'id');
    }

    public function getIdentifier(StationIdentifierType $type): ?StationIdentifier
    {
        if ($this->relationLoaded('stationIdentifiers')) {
            return $this->stationIdentifiers->firstWhere('type', $type) ?? null;
        }

        return $this->stationIdentifiers()->where('type', $type->value)->first();
    }

    public function getIdentifiers(StationIdentifierType $type): Collection
    {
        if ($this->relationLoaded('stationIdentifiers')) {
            return $this->stationIdentifiers->where('type', $type);
        }

        return $this->stationIdentifiers()->where('type', $type->value)->get();
    }

    public function stopovers(): HasMany
    {
        return $this->hasMany(Stopover::class, 'station_id', 'id');
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'areas_stations_maps')
            ->withPivot('default')
            ->using(AreasStationsMap::class);
    }
}
