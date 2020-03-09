<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HafasTrip extends Model
{
    /*   protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'origin', 'destination', 'departure', 'arrival', 'stopovers'
    ];
    */
    protected $hidden = ['created_at', 'updated_at', 'polyline'];

    public function getPolyLine()
    {
        return $this->hasOne('App\PolyLine', 'hash', 'polyline');
    }

}
