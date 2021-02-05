<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class TrainCheckin extends Model
{
    protected $fillable = [
        'status_id', 'trip_id', 'origin', 'destination',
        'distance', 'delay', 'points', 'departure', 'arrival'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $dates    = ['departure', 'arrival', 'created_at', 'updated_at'];
    protected $appends  = ['duration', 'origin_stopover', 'destination_stopover', 'speed'];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
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

    public function getOriginStopoverAttribute(): ?TrainStopover {
        return $this->HafasTrip->stopoversNEW->where('train_station_id', $this->Origin->id)
                                             ->where('departure_planned', $this->departure->toIso8601String())
                                             ->first();
    }

    public function getDestinationStopoverAttribute(): ?TrainStopover {
        return $this->HafasTrip->stopoversNEW->where('train_station_id', $this->Destination->id)
                                             ->where('arrival_planned', $this->arrival->toIso8601String())
                                             ->first();
    }

    public function getMapLines() {
        $hafas = $this->HafasTrip->getPolyLine;
        if ($hafas === null) {
            $origin = $this->Origin;

            $destination = $this->Destination;
            $route       = [];
            $route[0]    = [$origin->longitude, $origin->latitude];
            $route[1]    = [$destination->longitude, $destination->latitude];

            return json_encode($route);
        }


        $polyline = json_decode($hafas->polyline);

        // Bei manchen Posts ist das Feld leer.
        if (!isset($polyline->features)) {
            return json_encode([]);
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
                $coords[]     = $f->geometry->coordinates;
            }

            // If this was the destination, don't loop any further.
            if (isset($f->properties->id) && $f->properties->id == $destination) {
                break;
            }

        }
        return json_encode($coords);
    }

    /**
     * The duration of the journey in minutes
     * @return int
     */
    public function getDurationAttribute(): int {try {
        return $this->origin_stopover->departure_planned->diffInMinutes($this->destination_stopover->arrival_planned);
    } catch (\Exception) {
        //We need the try-catch to support old checkins, where no stopovers are saved.
        return $this->arrival->diffInMinutes($this->departure);
    }
    }

    public function getSpeedAttribute(): float {
        return $this->duration == 0 ? 0 : $this->distance / ($this->duration / 60);
    }

    public function getAlsoOnThisConnectionAttribute(): Collection {
        return TrainCheckin::with(['status'])
                           ->where([
                                       ['status_id', '<>', $this->status->id],
                                       ['trip_id', '=', $this->HafasTrip->trip_id],
                                       ['arrival', '>', $this->departure],
                                       ['departure', '<', $this->arrival]
                                   ])
                           ->get()
                           ->map(function($trainCheckIn) {
                               return $trainCheckIn->status;
                           });
    }
}
