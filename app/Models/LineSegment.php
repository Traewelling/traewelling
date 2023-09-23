<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LineSegment
 * @property bool $reversible
 * @property int  $distance Distance in meters
 */
class LineSegment extends Model
{
    use HasFactory;

    protected $fillable = ['reversible', 'distance'];
    protected $casts    = [
        'reversible' => 'boolean',
        'distance'   => 'integer',
    ];
}
