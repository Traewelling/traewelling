<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LineSegmentPoint
 * @property int $segment_id
 * @property float $latitude
 * @property float $longitude
 */
class LineSegmentPoint extends Model
{
    use HasFactory;

    protected $fillable = ['segment_id', 'latitude', 'longitude'];
}
