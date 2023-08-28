<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $origin_id
 * @property int $destination_id
 * @property int $segment_id
 * @property bool $reversed
 * @property TrainStation $origin
 * @property TrainStation $destination
 * @property LineSegment $segment
 */
class LineSegmentBetween extends Model
{
    use HasFactory;

    protected $fillable = [
        'origin_id', 'destination_id', 'segment_id', 'reversed'
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $table = 'line_segment_between';

    public function origin(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'origin_id');
    }

    public function destination(): HasOne {
        return $this->hasOne(TrainStation::class, 'id', 'destination_id');
    }

    public function segment(): HasOne {
        return $this->hasOne(LineSegment::class, 'id', 'segment_id');
    }
}
