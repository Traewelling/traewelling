<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainCheckin extends Model
{
    protected $fillable = [
        'status_id', 'trip_id', 'origin', 'destination',
        'distance', 'delay', 'points', 'departure', 'arrival'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $dates    = ['departure', 'arrival', 'created_at', 'updated_at'];
    protected $appends  = ['duration'];

    public function status() {
        return $this->belongsTo(Status::class);
    }

    public function Origin() {
        return $this->hasOne(TrainStation::class, 'ibnr', 'origin');
    }

    public function Destination() {
        return $this->hasOne(TrainStation::class, 'ibnr', 'destination');
    }

    public function HafasTrip() {
        return $this->hasOne(HafasTrip::class, 'trip_id', 'trip_id');
    }

    public function getMapLines() {
        $hafas = $this->HafasTrip()->first()->getPolyLine()->first();
        if ($hafas === null) {
            $origin = $this->Origin()->first();

            $destination = $this->Destination()->first();
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
    public function getDurationAttribute() {
        return $this->arrival->diffInMinutes($this->departure);
    }

    public function getAlsoOnThisConnectionAttribute() {
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
