<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use App\Dto\Coordinate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @todo rename table to "Stopover" (without Train - we have more than just trains)
 * @todo rename "train_station_id" to "station_id" - we have more than just trains.
 * @todo rename "cancelled" to "is_cancelled" - or split into "is_arrival_cancelled" and "is_departure_cancelled"? need
 *       to think about this.
 *
 * @property int $id
 * @property string $trip_id
 * @property int $train_station_id
 * @property $arrival_planned
 * @property $arrival_real
 * @property string|null $arrival_platform_planned
 * @property string|null $arrival_platform_real
 * @property $departure_planned
 * @property $departure_real
 * @property string|null $departure_platform_planned
 * @property string|null $departure_platform_real
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $cancelled
 * @property string|null $route_segment_id
 * @property string|null $station_identifier_id
 * @property-read Carbon|null $arrival
 * @property-read Coordinate|null $coordinate
 * @property-read Carbon|null $departure
 * @property-read bool $is_arrival_cancelled
 * @property-read bool $is_arrival_delayed
 * @property-read bool $is_departure_cancelled
 * @property-read bool $is_departure_delayed
 * @property-read string|null $platform
 * @property-read RouteSegment|null $routeSegment
 * @property-read Station $station
 * @property-read StationIdentifier|null $stationIdentifier
 * @property-read Station $trainStation
 * @property-read Trip $trip
 *
 * @method static \Database\Factories\StopoverFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalPlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalPlatformPlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalPlatformReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereArrivalReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDeparturePlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDeparturePlatformPlanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDeparturePlatformReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereDepartureReal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereRouteSegmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereStationIdentifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereTrainStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Stopover whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Stopover extends Model
{
    use HasFactory;

    protected $table = 'train_stopovers';

    protected $fillable = [
        'trip_id', 'train_station_id',
        'arrival_planned', 'arrival_real',
        'arrival_platform_planned', 'arrival_platform_real',
        'departure_planned', 'departure_real',
        'departure_platform_planned', 'departure_platform_real',
        'cancelled', 'station_identifier_id', 'route_segment_id',
    ];

    protected $appends = [
        'arrival', 'departure', 'platform', 'isArrivalDelayed', 'isDepartureDelayed',
        'isArrivalCancelled', 'isDepartureCancelled',
    ];

    protected $casts = [
        'id' => 'integer',
        'trip_id' => 'string',
        'train_station_id' => 'integer',
        'arrival_planned' => UTCDateTime::class,
        'arrival_real' => UTCDateTime::class,
        'arrival_platform_planned' => 'string',
        'arrival_platform_real' => 'string',
        'departure_planned' => UTCDateTime::class,
        'departure_real' => UTCDateTime::class,
        'departure_platform_planned' => 'string',
        'departure_platform_real' => 'string',
        'cancelled' => 'boolean',
        'route_segment_id' => 'string',
        'station_identifier_id' => 'string',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id', 'trip_id');
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'train_station_id', 'id');
    }

    public function routeSegment(): HasOne
    {
        return $this->hasOne(RouteSegment::class, 'id', 'route_segment_id');
    }

    /**
     * @deprecated use station() instead
     */
    public function trainStation(): BelongsTo
    {
        return $this->station();
    }

    public function stationIdentifier(): HasOne
    {
        return $this->hasOne(StationIdentifier::class, 'id', 'station_identifier_id');
    }

    public function getArrivalAttribute(): ?Carbon
    {
        // Fallback to departure timestamps directly to avoid mutual recursion with getDepartureAttribute.
        return $this->arrival_real ?? $this->arrival_planned
            ?? $this->departure_real ?? $this->departure_planned;
    }

    public function getDepartureAttribute(): ?Carbon
    {
        // Fallback to arrival timestamps directly to avoid mutual recursion with getArrivalAttribute.
        return $this->departure_real ?? $this->departure_planned
            ?? $this->arrival_real ?? $this->arrival_planned;
    }

    public function getPlatformAttribute(): ?string
    {
        return ($this->departure_platform_real ?? $this->arrival_platform_planned) ??
               ($this->arrival_platform_real ?? $this->departure_platform_planned);
    }

    public function getIsArrivalDelayedAttribute(): bool
    {
        if ($this->arrival_real == null || $this->arrival_planned == null) {
            return false;
        }

        return $this->arrival_real->isAfter($this->arrival_planned);
    }

    public function getIsDepartureDelayedAttribute(): bool
    {
        if ($this->departure_real == null || $this->departure_planned == null) {
            return false;
        }

        return $this->departure_real->isAfter($this->departure_planned);
    }

    public function getIsArrivalCancelledAttribute(): bool
    {
        return $this->cancelled && is_null($this->arrival_platform_planned);
    }

    public function getIsDepartureCancelledAttribute(): bool
    {
        return $this->cancelled && is_null($this->departure_platform_planned);
    }

    /**
     * Position of this stop. The identifier describes the concrete stop a provider routes through,
     * so it takes precedence over the merged station it belongs to.
     */
    public function getCoordinateAttribute(): ?Coordinate
    {
        $location = $this->stationIdentifier?->location ?? $this->station?->location;

        if ($location === null) {
            return null;
        }

        return new Coordinate($location->latitude, $location->longitude);
    }

    /**
     * Planned travel time in seconds from this stop to a later one. Returns -1 when either side
     * carries no usable timestamp, which is the value a RouteSegment stores for an unknown duration.
     */
    public function plannedSecondsUntil(self $destination): int
    {
        $startTime = $this->departure_planned ?? $this->arrival_planned;
        $endTime = $destination->arrival_planned ?? $destination->departure_planned;

        if (!$startTime?->isValid() || !$endTime?->isValid()) {
            return -1;
        }

        return (int) round($startTime->diffInSeconds($endTime));
    }
}
