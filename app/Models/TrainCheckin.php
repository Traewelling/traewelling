<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * @property int           $id
 * @property int           $status_id
 * @property HafasTrip     $HafasTrip
 * @property TrainStopover $origin_stopover
 * @property TrainStopover $destination_stopover
 * @property TrainStation  $originStation
 * @property TrainStation  $destinationStation
 */
class TrainCheckin extends Model
{

    use HasFactory;

    protected $fillable = [
        'status_id', 'user_id', 'trip_id', 'origin_id', 'destination_id',
        'distance', 'duration', 'departure', 'real_departure', 'arrival', 'real_arrival', 'points', 'forced',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['origin_stopover', 'destination_stopover', 'speed'];
    protected $casts    = [
        'id'             => 'integer',
        'status_id'      => 'integer',
        'user_id'        => 'integer',
        'origin_id'      => 'integer',
        'destination_id' => 'integer',
        'distance'       => 'integer',
        'duration'       => 'integer',
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

    public function originStation(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'origin_id');
    }

    public function destinationStation(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'destination_id');
    }

    public function HafasTrip(): HasOne {
        return $this->hasOne(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function getOriginStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopovers->where('train_station_id', $this->originStation->id)
                                               ->where('departure_planned', $this->departure)
                                               ->first();
        if ($stopOver == null) {
            //To support legacy data, where we don't save the stopovers in the stopovers table, yet.
            Log::error('TrainCheckin #' . $this->id . ': Origin stopover not found. Created a new one.');
            $stopOver = TrainStopover::updateOrCreate(
                [
                    "trip_id"          => $this->trip_id,
                    "train_station_id" => $this->originStation->id
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
        $stopOver = $this->HafasTrip->stopovers->where('train_station_id', $this->destinationStation->id)
                                               ->where('arrival_planned', $this->arrival)
                                               ->first();
        if ($stopOver == null) {
            //To support legacy data, where we don't save the stopovers in the stopovers table, yet.
            Log::error('TrainCheckin #' . $this->id . ': Destination stopover not found. Created a new one.');
            $stopOver = TrainStopover::updateOrCreate(
                [
                    "trip_id"          => $this->trip_id,
                    "train_station_id" => $this->destinationStation->id
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
     * Overwrite the default getter to return the cached value if available
     * @return int
     */
    public function getDurationAttribute(): int {
        //If the duration is already calculated and saved, return it
        if (isset($this->attributes['duration']) && $this->attributes['duration'] !== null) {
            return $this->attributes['duration'];
        }

        //Else calculate and cache it
        $departure = $this->real_departure ?? $this->origin_stopover->departure ?? $this->departure;
        $arrival   = $this->real_arrival ?? $this->destination_stopover->arrival ?? $this->arrival;
        $duration  = $arrival->diffInMinutes($departure);
        DB::table('train_checkins')->where('id', $this->id)->update(['duration' => $duration]);
        return $duration;
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
