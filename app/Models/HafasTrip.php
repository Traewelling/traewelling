<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HafasTrip extends Model
{

    use HasFactory;

    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'origin', 'destination',
        'stopovers', 'polyline', 'departure', 'arrival', 'delay'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $dates    = ['departure', 'arrival', 'created_at', 'updated_at'];

    public function getPolyLine() {
        return $this->hasOne(PolyLine::class, 'hash', 'polyline');
    }

    public function originStation() {
        return $this->belongsTo(TrainStation::class, 'origin', 'ibnr');
    }

    public function destinationStation() {
        return $this->belongsTo(TrainStation::class, 'destination', 'ibnr');
    }
}