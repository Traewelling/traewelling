<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{

    protected $fillable = ['name', 'hashtag', 'trainstation', 'slug', 'host', 'url', 'begin', 'end'];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['trainDistance', 'trainDuration'];
    protected $casts    = [
        'id'           => 'integer',
        'trainstation' => 'integer',
        'begin'        => 'datetime',
        'end'          => 'datetime',
    ];

    /**
     * @return HasOne
     * @todo rename to ->trainStation when variable is renamed in database to train_station_id
     */
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
     * @deprecated Use ->station relationship instead
     */
    public function getTrainstation(): TrainStation {
        return TrainStation::where("id", "=", $this->trainstation)->first() ?? new TrainStation();
    }
}
