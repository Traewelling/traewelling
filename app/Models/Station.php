<?php

namespace App\Models;

use App\StationIdentifierType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use phpGPX\Models\Point;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @todo rename table to "Station" (without Train - we have more than just trains)
 */
class Station extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'train_stations';

    protected $fillable = [
        'relevance', 'name', 'latitude', 'longitude', 'source', 'time_offset', 'shift_time',
    ];

    protected $hidden = ['created_at', 'updated_at', 'time_offset', 'shift_time'];

    protected $casts = [
        'id' => 'integer',
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
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }

    public function stationIdentifiers(): HasMany
    {
        return $this->hasMany(StationIdentifier::class, 'station_id', 'id');
    }

    /**
     * Get a specific identifier by type (returns first match if multiple exist)
     */
    public function getIdentifier(StationIdentifierType $type): ?string
    {
        /** @var StationIdentifier|null $stationIdentifier */
        $stationIdentifier = $this->stationIdentifiers
            ->where('type', $type)
            ->first();

        return $stationIdentifier?->identifier;
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
