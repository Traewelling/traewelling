<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainStopOver extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'train_station_id',
        'arrival_planned', 'arrival_real',
        'arrival_platform_planned', 'arrival_platform_real',
        'departure_planned', 'departure_real',
        'departure_platform_planned', 'departure_platform_real',
    ];
    protected $dates    = [
        'arrival_planned', 'arrival_real',
        'departure_planned', 'departure_real',
    ];
    protected $appends  = [
        'arrival', 'departure', 'platform',
        'isArrivalDelayed', 'isDepartureDelayed'
    ];

    public function trip(): BelongsTo {
        return $this->belongsTo(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function trainStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'train_station_id', 'id');
    }

    public function getArrivalAttribute(): ?Carbon {
        return ($this->arrival_real ?? $this->arrival_planned) ?? $this->departure;
    }

    public function getDepartureAttribute(): ?Carbon {
        return ($this->departure_real ?? $this->departure_planned) ?? $this->arrival;
    }

    public function getPlatformAttribute(): ?string {
        return $this->departure_platform_real ?? $this->departure_platform_planned;
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
}
