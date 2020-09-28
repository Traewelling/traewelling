<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HafasTrip extends Model
{

    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function getPolyLine() {
        return $this->hasOne(PolyLine::class, 'hash', 'polyline');
    }
}
