<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class TrainCheckin extends Model
{
    protected $fillable = [
        'status_id', 'user_id', 'trip_id', 'origin', 'destination',
        'distance', 'departure', 'arrival', 'points',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['duration', 'origin_stopover', 'destination_stopover', 'speed'];
    protected $casts    = [
        'id'          => 'integer',
        'status_id'   => 'integer',
        'user_id'     => 'integer',
        'origin'      => 'integer',
        'destination' => 'integer',
        'distance'    => 'integer',
        'departure'   => 'datetime',
        'arrival'     => 'datetime',
        'points'      => 'integer',
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function Origin(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'origin');
    }

    public function Destination(): HasOne {
        return $this->hasOne(TrainStation::class, 'ibnr', 'destination');
    }

    public function HafasTrip(): HasOne {
        return $this->hasOne(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function getOriginStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopoversNEW->where('train_station_id', $this->Origin->id)
                                                  ->where('departure_planned', $this->departure)
                                                  ->first();
        if ($stopOver == null) {
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
            $this->HafasTrip->load('stopoversNEW');
        }
        return $stopOver;
    }

    public function getDestinationStopoverAttribute(): TrainStopover {
        $stopOver = $this->HafasTrip->stopoversNEW->where('train_station_id', $this->Destination->id)
                                                  ->where('arrival_planned', $this->arrival)
                                                  ->first();
        if ($stopOver == null) {
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
            $this->HafasTrip->load('stopoversNEW');
        }
        return $stopOver;
    }

    /**
     * @return array
     * @deprecated I have not found any use. It can be removed in my opinion.
     *             ~ kr
     */
    public function getMapLines(): array {
        $hafas = $this->HafasTrip->polyline;
        if ($hafas === null) {
            $origin = $this->Origin;

            $destination = $this->Destination;
            $route       = [];
            $route[0]    = [$origin->latitude, $origin->longitude];
            $route[1]    = [$destination->latitude, $destination->longitude];

            return $route;
        }


        $polyline = json_decode($hafas->polyline);

        // Bei manchen Posts ist das Feld leer.
        if (!isset($polyline->features)) {
            return [];
        }

        $features     = $polyline->features;
        $coords       = [];
        $origin       = $this->origin;
        $destination  = $this->destination;
        $behindOrigin = false;

        foreach ($features as $f) {
            // Check if this point is the trips origin => Include this point!
            if ($behindOrigin || (isset($f->properties->id) && $f->properties->id == $origin)) {
                $behindOrigin = true;
                $coords[]     = [$f->geometry->coordinates[1], $f->geometry->coordinates[0]];
            }

            // If this was the destination, don't loop any further.
            if (isset($f->properties->id) && $f->properties->id == $destination) {
                break;
            }

        }
        return $coords;
    }

    /**
     * The duration of the journey in minutes
     * @return int
     */
    public function getDurationAttribute(): int {
        try {
            return $this->origin_stopover->departure_planned->diffInMinutes(
                $this->destination_stopover->arrival_planned
            );
        } catch (Exception) {
            //We need the try-catch to support old checkins, where no stopovers are saved.
            return $this->arrival->diffInMinutes($this->departure);
        }
    }

    public function getSpeedAttribute(): float {
        return $this->duration == 0 ? 0 : ($this->distance / 1000) / ($this->duration / 60);
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
                   ->map(function($trainCheckIn) {
                       return $trainCheckIn->status;
                   })
                   ->filter(function($status) {
                       return $status !== null;
                   });
    }
}
