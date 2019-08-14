<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainCheckin extends Model
{
    public function Status () {
        return $this->hasOne('App\Status');
    }

    public function TrainStation () {
        return $this->hasMany('App\TrainStations');
    }

    public function HafasTrip() {
        return $this->hasone('App\HafasTrip');
    }
}
