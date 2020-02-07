<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model {
    protected $dates = [
        'begin',
        'end'
    ];

    protected $fillable = [
        'name',
        'hashtag',
        'slug',
        'host',
        'url',
        'begin',
        'end'
    ];

    public function trainstation() {
        return $this->hasOne('App\TrainStations', 'trainstation', 'id');
    }
    public function getTrainstation(): TrainStations {
        return TrainStations::where("id", "=", $this->trainstation)->first() ?? new TrainStations();
    }
}
