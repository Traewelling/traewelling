<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use App\Enum\TimeType;
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
 * //properties
 * @property int           $id
 * @property int           $status_id
 * @property int           $user_id
 * @property string        $trip_id
 * @property int           $origin      @deprecated -> use origin_stopover instead
 * @property int           $origin_stopover_id
 * @property int           $destination @deprecated -> use destination_stopover instead
 * @property int           $destination_stopover_id
 * @property int           $distance
 * @property int           $duration
 * @property UTCDateTime   $departure   @deprecated -> use origin_stopover instead
 * @property UTCDateTime   $manual_departure
 * @property UTCDateTime   $arrival     @deprecated -> use destination_stopover instead
 * @property UTCDateTime   $manual_arrival
 * @property int           $points
 * @property bool          $forced
 *
 * //relations
 * @property HafasTrip     $HafasTrip
 * @property TrainStopover $origin_stopover
 * @property TrainStopover $destination_stopover
 * @property Status        $status
 * @property User          $user
 * @property TrainStation  $originStation
 * @property TrainStopover $originStopoverRelation
 * @property TrainStation  $destinationStation
 * @property TrainStopover $destinationStopoverRelation
 *
 * @todo rename table to "Checkin" (without Train - we have more than just trains)
 * @todo merge model with "Status" because the difference between trip sources (HAFAS,
 *        User, and future sources) should be handled in the Trip model.
 * @todo use the `id` from trips, instead of the hafas trip id - this is duplicated data
 * @todo drop the `origin`, `destination`, `departure` and `arrival` columns and use the stopover instead
 */
class TrainCheckin extends Model
{

    use HasFactory;

    protected $fillable = [
        'status_id', 'user_id', 'trip_id', 'origin', 'origin_stopover_id', 'destination', 'destination_stopover_id',
        'distance', 'duration', 'departure', 'manual_departure', 'arrival', 'manual_arrival', 'points', 'forced',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['origin_stopover', 'destination_stopover', 'speed'];
    protected $casts    = [
        'id'                      => 'integer',
        'status_id'               => 'integer',
        'user_id'                 => 'integer',
        'origin'                  => 'integer', //@deprecated -> use origin_stopover_id instead
        'origin_stopover_id'      => 'integer',
        'destination'             => 'integer', //@deprecated -> use destination_stopover_id instead
        'destination_stopover_id' => 'integer',
        'distance'                => 'integer',
        'duration'                => 'integer',
        'departure'               => UTCDateTime::class, //@deprecated -> use origin_stopover_id instead
        'manual_departure'        => UTCDateTime::class,
        'arrival'                 => UTCDateTime::class, //@deprecated -> use destination_stopover_id instead
        'manual_arrival'          => UTCDateTime::class,
        'points'                  => 'integer',
        'forced'                  => 'boolean',
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    /**
     * @return BelongsTo
     * @todo rename to `originStopover` when `getOriginStopoverAttribute` is removed
     */
    public function originStopoverRelation(): BelongsTo {
        return $this->belongsTo(TrainStopover::class, 'origin_stopover_id', 'id');
    }

    /**
     * @return TrainStopover
     * @todo make this a relation (hasOne) when the origin and destination columns are removed
     */
    public function getOriginStopoverAttribute(): TrainStopover {
        if ($this->origin_stopover_id !== null) {
            return $this->originStopoverRelation;
        }

        $stopover = $this->HafasTrip->stopovers->where('train_station_id', $this->originStation->id)
                                               ->where('departure_planned', $this->departure)
                                               ->first();
        if ($stopover === null) {
            //To support legacy data, where we don't save the stopovers in the stopovers table, yet.
            Log::info('TrainCheckin #' . $this->id . ': Origin stopover not found. Created a new one.');
            $stopover = TrainStopover::updateOrCreate(
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
        //  $this->update(['origin_stopover_id' => $stopover->id]);
        return $stopover;
    }

    /**
     * @return BelongsTo
     * @todo rename to `destinationStopover` when `getDestinationStopoverAttribute` is removed
     */
    public function destinationStopoverRelation(): BelongsTo {
        return $this->belongsTo(TrainStopover::class, 'destination_stopover_id', 'id');
    }

    /**
     * @return TrainStopover
     * @todo make this a relation (hasOne) when the origin and destination columns are removed
     */
    public function getDestinationStopoverAttribute(): TrainStopover {
        if ($this->destination_stopover_id !== null) {
            return $this->destinationStopoverRelation;
        }

        $stopover = $this->HafasTrip->stopovers->where('train_station_id', $this->destinationStation->id)
                                               ->where('arrival_planned', $this->arrival)
                                               ->first();
        if ($stopover === null) {
            //To support legacy data, where we don't save the stopovers in the stopovers table, yet.
            Log::info('TrainCheckin #' . $this->id . ': Destination stopover not found. Created a new one.');
            $stopover = TrainStopover::updateOrCreate(
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
//        $this->update(['destination_stopover_id' => $stopover->id]);
        return $stopover;
    }

    /**
     * Takes the planned and optionally real and manual times and returns an array to use while displaying the status.
     * Precedence: Manual > Real > Planned.
     * Only returns the 'original' planned time if the updated time differs from the planned time.
     */
    private static function prepareDisplayTime($planned, $real = null, $manual = null): array {
        if (isset($manual)) {
            $time = $manual;
            $type = TimeType::MANUAL;
        } elseif (isset($real)) {
            $time = $real;
            $type = TimeType::REALTIME;
        } else {
            $time = $planned;
            $type = TimeType::PLANNED;
        }
        return [
            'time'     => $time,
            'original' => ($planned->toString() !== $time->toString()) ? $planned : null,
            'type'     => $type
        ];
    }

    public function getDisplayDepartureAttribute(): \stdClass {
        $planned = $this->origin_stopover?->departure_planned
                   ?? $this->origin_stopover?->departure
                      ?? $this->departure;
        $real    = $this->origin_stopover?->departure_real;
        $manual  = $this->manual_departure;
        return (object) self::prepareDisplayTime($planned, $real, $manual);
    }

    public function getDisplayArrivalAttribute(): \stdClass {
        $planned = $this->destination_stopover?->arrival_planned
                   ?? $this->destination_stopover?->arrival
                      ?? $this->arrival;
        $real    = $this->destination_stopover?->arrival_real;
        $manual  = $this->manual_arrival;
        return (object) self::prepareDisplayTime($planned, $real, $manual);
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
        $departure = $this->manual_departure ?? $this->origin_stopover->departure ?? $this->departure;
        $arrival   = $this->manual_arrival ?? $this->destination_stopover->arrival ?? $this->arrival;
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
