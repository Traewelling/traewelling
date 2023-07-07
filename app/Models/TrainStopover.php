<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainStopover extends Model
{
    use HasFactory;

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
        'departure_platform_real'    => 'string'
    ];

    public function trip(): BelongsTo {
        return $this->belongsTo(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function trainStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'train_station_id', 'id');
    }

    // These two methods are a ticking time bomb and I hope we'll never see it explode. 💣
    public function getArrivalAttribute(): ?Carbon {
        return ($this->arrival_real ?? $this->arrival_planned) ?? $this->departure;
    }

    public function getDepartureAttribute(): ?Carbon {
        return ($this->departure_real ?? $this->departure_planned) ?? $this->arrival;
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
