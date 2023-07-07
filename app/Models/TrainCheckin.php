<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $status_id
 * @property HafasTrip $HafasTrip
 * @property TrainStopover $origin_stopover
 * @property TrainStopover $destination_stopover
 */
class TrainCheckin extends Model
{

    use HasFactory;

    protected $fillable = [
        'status_id', 'user_id', 'trip_id', 'origin', 'destination',
        'distance', 'departure', 'real_departure', 'arrival', 'real_arrival', 'points', 'forced',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['duration', 'origin_stopover', 'destination_stopover', 'speed'];
    protected $casts    = [
        'id'             => 'integer',
        'status_id'      => 'integer',
        'user_id'        => 'integer',
        'origin'         => 'integer',
        'destination'    => 'integer',
        'distance'       => 'integer',
        'departure'      => UTCDateTime::class,
        'real_departure' => UTCDateTime::class,
        'arrival'        => UTCDateTime::class,
        'real_arrival'   => UTCDateTime::class,
        'points'         => 'integer',
        'forced'         => 'boolean',
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @deprecated Conflicts with the variable 'origin'. Use ->originStation instead.
     */
    public function Origin(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'origin');
    }

    /**
     * @deprecated Conflicts with the variable 'destination'. Use ->destinationStation instead.
     */
    public function Destination(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'destination');
    }

    public function originStation(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'origin');
    }

    public function destinationStation(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'destination');
    }

    public function HafasTrip(): HasOne {
        return $this->hasOne(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function getOriginStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopovers->where('train_station_id', $this->Origin->id)
                                               ->where('departure_planned', $this->departure)
                                               ->first();
        if ($stopOver == null) {
            //To support legacy data, where we don't save the stopovers in the stopovers table, yet.
            Log::error('TrainCheckin #' . $this->id . ': Origin stopover not found. Created a new one.');
            $stopOver = TrainStopover::updateOrCreate(
                [
                    "trip_id"          => $this->trip_id,
                    "train_station_id" => $this->Origin->id
                ],
                [
                    "departure_planned" => $this->departure,
                    "arrival_planned"   => $this->departure,
                ]
            );
            $this->HafasTrip->load('stopovers');
        }
        return $stopOver;
    }

    public function getDestinationStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopovers->where('train_station_id', $this->Destination->id)
                                               ->where('arrival_planned', $this->arrival)
                                               ->first();
        if ($stopOver == null) {
            //To support legacy data, where we don't save the stopovers in the stopovers table, yet.
            Log::error('TrainCheckin #' . $this->id . ': Destination stopover not found. Created a new one.');
            $stopOver = TrainStopover::updateOrCreate(
                [
                    "trip_id"          => $this->trip_id,
                    "train_station_id" => $this->Destination->id
                ],
                [
                    "departure_planned" => $this->arrival,
                    "arrival_planned"   => $this->arrival,
                ]
            );
            $this->HafasTrip->load('stopovers');
        }
        return $stopOver;
    }

    /**
     * The duration of the journey in minutes
     * @return int
     */
    public function getDurationAttribute(): int {
        return ($this->real_arrival ?? $this->arrival)->diffInMinutes($this->real_departure ?? $this->departure);
    }

    public function getSpeedAttribute(): float {
        return $this->duration === 0 ? 0 : ($this->distance / 1000) / ($this->duration / 60);
    }

    /**
     * @return Collection
     * @todo Sichtbarkeit der CheckIns prüfen! Hier werden auch Private CheckIns angezeigt
     */
    public function getAlsoOnThisConnectionAttribute(): Collection {
        return self::with(['status'])
                   ->where([
                               ['status_id', '<>', $this->status->id],
                               ['trip_id', '=', $this->HafasTrip->trip_id],
                               ['arrival', '>', $this->departure],
                               ['departure', '<', $this->arrival]
                           ])
                   ->get()
                   ->map(function(TrainCheckin $trainCheckin) {
                       return $trainCheckin->status;
                   })
                   ->filter(function($status) {
                       return $status !== null && Gate::forUser(Auth::user())->allows('view', $status);
                   });
    }
}
