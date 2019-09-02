<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainCheckin extends Model
{
    public function status () {
        return $this->belongsTo('App\Status');
    }

    public function getOrigin () {
        return $this->hasOne('App\TrainStations','ibnr', 'origin');
    }

    public function getDestination () {
        return $this->hasOne('App\TrainStations', 'ibnr' ,'destination');
}

    public function getHafasTrip() {
        return $this->hasone('App\HafasTrip', 'trip_id', 'trip_id');
    }

    public function getMapLines() {

        $hafas = $this->getHafasTrip()->get()->get(0);
        $polyline = json_decode($hafas->polyline);
        $features = $polyline->features;
        $coords = [];

        $origin = $this->origin;
        $destination = $this->destination;

        $behindOrigin = false;
        foreach ($features as $f) {
            // Check if this point is the trips origin => Include this point!
            if ($behindOrigin || (isset($f->properties->id) && $f->properties->id == $origin)) {
                $behindOrigin = true;

                $coords[] = $f->geometry->coordinates;
            }

            // If this was the destination, don't loop any further.
            if (isset($f->properties->id) && $f->properties->id == $destination) {
                break;
            }

        }

        return json_encode($coords);
    }
}
