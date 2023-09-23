<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\LineSegment
 * @property string $hash
 * @property int    $line_segment_id
 */
class LineRun extends Model
{
    use HasFactory;

    protected $fillable = ['hash', 'line_segment_id'];
}
