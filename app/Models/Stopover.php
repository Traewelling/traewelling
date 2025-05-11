<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use Carbon\Carbon;
use Database\Factories\StopoverFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @todo rename table to "Stopover" (without Train - we have more than just trains)
 * @todo rename "train_station_id" to "station_id" - we have more than just trains.
 * @todo rename "cancelled" to "is_cancelled" - or split into "is_arrival_cancelled" and "is_departure_cancelled"? need
 *       to think about this.
 * @property int                             $id
 * @property string                          $trip_id
 * @property int                             $train_station_id
 * @property $arrival_planned
 * @property $arrival_real
 * @property string|null                     $arrival_platform_planned
 * @property string|null                     $arrival_platform_real
 * @property $departure_planned
 * @property $departure_real
 * @property string|null                     $departure_platform_planned
 * @property string|null                     $departure_platform_real
 * @property bool                            $cancelled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Carbon|null                $arrival
 * @property-read Carbon|null                $departure
 * @property-read bool                       $is_arrival_cancelled
 * @property-read bool                       $is_arrival_delayed
 * @property-read bool                       $is_departure_cancelled
 * @property-read bool                       $is_departure_delayed
 * @property-read string|null                $platform
 * @property-read Station                    $station
 * @property-read Station                    $trainStation
 * @property-read Trip                       $trip
 * @method static StopoverFactory factory($count = null, $state = [])
 * @method static Builder<static>|Stopover newModelQuery()
 * @method static Builder<static>|Stopover newQuery()
 * @method static Builder<static>|Stopover query()
 * @method static Builder<static>|Stopover whereArrivalPlanned($value)
 * @method static Builder<static>|Stopover whereArrivalPlatformPlanned($value)
 * @method static Builder<static>|Stopover whereArrivalPlatformReal($value)
 * @method static Builder<static>|Stopover whereArrivalReal($value)
 * @method static Builder<static>|Stopover whereCancelled($value)
 * @method static Builder<static>|Stopover whereCreatedAt($value)
 * @method static Builder<static>|Stopover whereDeparturePlanned($value)
 * @method static Builder<static>|Stopover whereDeparturePlatformPlanned($value)
 * @method static Builder<static>|Stopover whereDeparturePlatformReal($value)
 * @method static Builder<static>|Stopover whereDepartureReal($value)
 * @method static Builder<static>|Stopover whereId($value)
 * @method static Builder<static>|Stopover whereTrainStationId($value)
 * @method static Builder<static>|Stopover whereTripId($value)
 * @method static Builder<static>|Stopover whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Stopover extends Model
{
    use HasFactory;

    protected $table    = 'train_stopovers';
    protected $fillable = [
        'trip_id', 'train_station_id',
        'arrival_planned', 'arrival_real',
        'arrival_platform_planned', 'arrival_platform_real',
        'departure_planned', 'departure_real',
        'departure_platform_planned', 'departure_platform_real',
        'cancelled'
    ];
    protected $appends  = [
        'arrival', 'departure', 'platform', 'isArrivalDelayed', 'isDepartureDelayed',
        'isArrivalCancelled', 'isDepartureCancelled'
    ];
    protected $casts    = [
        'id'                         => 'integer',
        'train_station_id'           => 'integer',
        'arrival_planned'            => UTCDateTime::class,
        'arrival_real'               => UTCDateTime::class,
        'arrival_platform_planned'   => 'string',
        'arrival_platform_real'      => 'string',
        'departure_planned'          => UTCDateTime::class,
        'departure_real'             => UTCDateTime::class,
        'departure_platform_planned' => 'string',
        'departure_platform_real'    => 'string',
        'isArrivalDelayed'           => 'boolean',
        'isDepartureDelayed'         => 'boolean',
        'cancelled'                  => 'boolean',
    ];

    public function trip(): BelongsTo {
        return $this->belongsTo(Trip::class, 'trip_id', 'trip_id');
    }

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class, 'train_station_id', 'id');
    }

    /**
     * @return BelongsTo
     * @deprecated use station() instead
     */
    public function trainStation(): BelongsTo {
        return $this->station();
    }

    // These two methods are a ticking time bomb and I hope we'll never see it explode. 💣
    public function getArrivalAttribute(): ?Carbon {
        return ($this->arrival_real ?? $this->arrival_planned) ?? $this?->departure;
    }

    public function getDepartureAttribute(): ?Carbon {
        return ($this->departure_real ?? $this->departure_planned) ?? $this?->arrival;
    }

    public function getPlatformAttribute(): ?string {
        return ($this->departure_platform_real ?? $this->arrival_platform_planned) ??
               ($this->arrival_platform_real ?? $this->departure_platform_planned);
    }

    public function getIsArrivalDelayedAttribute(): bool {
        if ($this->arrival_real == null || $this->arrival_planned == null) {
            return false;
        }
        return $this->arrival_real->isAfter($this->arrival_planned);
    }

    public function getIsDepartureDelayedAttribute(): bool {
        if ($this->departure_real == null || $this->departure_planned == null) {
            return false;
        }
        return $this->departure_real->isAfter($this->departure_planned);
    }

    public function getIsArrivalCancelledAttribute(): bool {
        return $this->cancelled && is_null($this->arrival_platform_planned);
    }

    public function getIsDepartureCancelledAttribute(): bool {
        return $this->cancelled && is_null($this->departure_platform_planned);
    }
}
