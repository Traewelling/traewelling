<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'address_street', 'address_zip', 'address_city', 'latitude', 'longitude'];
    protected $casts    = [
        'name'           => 'string',
        'address_street' => 'string',
        'address_zip'    => 'string',
        'address_city'   => 'string',
        'latitude'       => 'float',
        'longitude'      => 'float',
    ];
}
