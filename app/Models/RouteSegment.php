<?php

namespace App\Models;

use App\Dto\Coordinate;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Traewelling\GooglePolyline\PolylineTranscoder;

/**
 * @property string $id
 * @property int $from_station_id
 * @property int $to_station_id
 * @property int $distance
 * @property int $duration
 * @property string|null $path_type
 * @property string $polyline
 * @property int $polyline_precision
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Station $fromStation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stopover> $stopOvers
 * @property-read int|null $stop_overs_count
 * @property-read \App\Models\Station $toStation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 *
 * @method static \Database\Factories\RouteSegmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereFromStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment wherePathType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment wherePolyline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment wherePolylinePrecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereToStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class RouteSegment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'from_station',
        'to_station',
        'distance',
        'duration',
        'polyline',
        'polyline_precision',
        'path_type',
        'custom_waypoints',
    ];

    protected $casts = [
        'custom_waypoints' => 'array',
    ];

    public function fromStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'from_station_id');
    }

    public function toStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'to_station_id');
    }

    public function stopOvers(): HasMany
    {
        return $this->hasMany(Stopover::class, 'route_segment_id');
    }

    public function trips(): HasManyThrough
    {
        return $this->hasManyThrough(Trip::class, Stopover::class, 'route_segment_id', 'trip_id', 'id', 'trip_id');
    }

    /**
     * @return Coordinate[]
     */
    public function getCoordinates(): array
    {
        $precision = $this->polyline_precision ?? 5;
        $locations = (new PolylineTranscoder())->decodePolyline($this->polyline, $precision);

        $coordinates = [];
        foreach ($locations as $key => $location) {
            $coordinates[] = new Coordinate($location->getLatitude(), $location->getLongitude());
            unset($locations[$key]);
        }

        return $coordinates;
    }
}
