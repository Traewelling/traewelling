<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{

    use HasFactory, LogsActivity;

    protected $fillable = [
        'name', 'hashtag', 'station_id', 'slug', 'host', 'url', 'begin', 'end', 'event_start', 'event_end'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $appends  = ['trainDistance', 'trainDuration', 'isPride'];
    protected $casts    = [
        'id'          => 'integer',
        'station_id'  => 'integer',
        'begin'       => 'datetime',
        'end'         => 'datetime',
        'event_start' => 'datetime',
        'event_end'   => 'datetime',
    ];

    public function station(): HasOne {
        return $this->hasOne(Station::class, 'id', 'station_id');
    }

    public function statuses(): HasMany {
        return $this->hasMany(Status::class);
    }

    public function getTrainDistanceAttribute(): float {
        return Checkin::whereIn('status_id', $this->statuses()->select('id'))
                      ->sum('distance');
    }

    public function getTrainDurationAttribute(): int {
        return Checkin::whereIn('status_id', $this->statuses()->select('id'))
                      ->sum('duration');
    }

    public function getIsPrideAttribute(): bool {
        $eventNameLowercase = strtolower($this->name);
        return Str::contains($eventNameLowercase, ['csd', 'pride']);
    }

    public function approvedBy(): HasOne {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->logOnlyDirty()
                         ->logOnly(['name', 'hashtag', 'station_id', 'slug', 'host', 'url', 'begin', 'end', 'event_start', 'event_end']);
    }
}
