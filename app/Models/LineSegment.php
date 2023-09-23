<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LineSegment
 * @property int  $distance Distance in meters
 */
class LineSegment extends Model
{
    use HasFactory;

    protected $fillable = ['distance'];
    protected $casts    = [
        'reversible' => 'boolean',
        'distance'   => 'integer',
    ];
}
