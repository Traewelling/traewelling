<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainStation extends Model
{

    use HasFactory;

    protected $fillable = ['ibnr', 'name', 'latitude', 'longitude'];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $casts    = [
        'latitude'  => 'double',
        'longitude' => 'double'
    ];
}
