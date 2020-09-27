<?php

namespace App\Models;

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

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function trainstation() {
        return $this->hasOne(TrainStations::class, 'trainstation', 'id');
    }

    public function getTrainstation(): TrainStations {
        return TrainStations::where("id", "=", $this->trainstation)->first() ?? new TrainStations();
    }
}
