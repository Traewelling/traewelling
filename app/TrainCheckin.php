<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainCheckin extends Model
{
    public function Status () {
        return $this->hasOne('App\Status');
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
}
