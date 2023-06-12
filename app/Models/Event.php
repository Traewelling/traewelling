<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Event extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'hashtag', 'station_id', 'slug', 'host', 'url', 'begin', 'end'];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['trainDistance', 'trainDuration', 'isPride'];
    protected $casts    = [
        'id'         => 'integer',
        'station_id' => 'integer',
        'begin'      => 'datetime',
        'end'        => 'datetime',
    ];

    public function station(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'station_id');
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class);
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

    public function getIsPrideAttribute(): bool {
        $event_name_lowercase = strtolower($this->name);
        return Str::contains($event_name_lowercase, ['csd', 'pride']);    
    }
}
