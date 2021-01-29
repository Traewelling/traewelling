<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HafasTrip extends Model
{

    use HasFactory;

    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'origin', 'destination',
        'stopovers', 'polyline', 'departure', 'arrival', 'delay'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $dates    = ['departure', 'arrival', 'created_at', 'updated_at'];

    public function getPolyLine(): HasOne {
        return $this->hasOne(PolyLine::class, 'hash', 'polyline');
    }

    public function originStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'origin', 'ibnr');
    }

    public function destinationStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'destination', 'ibnr');
    }
}