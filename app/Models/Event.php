<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{

    protected $fillable = ['name', 'hashtag', 'trainstation', 'slug', 'host', 'url', 'begin', 'end'];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $dates    = ['begin', 'end'];

    public function trainstation(): HasOne {
        return $this->hasOne(TrainStation::class, 'trainstation', 'id');
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class, 'event_id', 'id')
                    ->with(['user',
                            'trainCheckin',
                            'trainCheckin.Origin',
                            'trainCheckin.Destination',
                            'trainCheckin.HafasTrip',
                            'event'])
                    ->withCount('likes')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * @return TrainStation
     * @deprecated Use ->trainstation relationship instead
     */
    public function getTrainstation(): TrainStation {
        return TrainStation::where("id", "=", $this->trainstation)->first() ?? new TrainStation();
    }
}
