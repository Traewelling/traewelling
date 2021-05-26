<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{

    protected $fillable = ['name', 'hashtag', 'slug', 'host', 'url', 'begin', 'end'];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $dates    = ['begin', 'end'];
    protected $appends  = ['trainDistance', 'trainDuration'];

    public function station(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'trainstation');
    }

    public function getTrainDistanceAttribute(): float {
        return TrainCheckin::whereIn('status_id', $this->statuses()->select('id'))
                           ->select('distance')
                           ->sum('distance');
    }

    public function getTrainDurationAttribute(): int {
        return TrainCheckin::whereIn('status_id', $this->statuses()->select('id'))
                           ->select(['arrival', 'departure'])
                           ->get()
                           ->sum('duration');
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class);
    }

    /**
     * @return TrainStation
     * @deprecated Use ->trainstation relationship instead
     */
    public function getTrainstation(): TrainStation {
        return TrainStation::where("id", "=", $this->trainstation)->first() ?? new TrainStation();
    }
}
