<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int     $id
 * @property int     $ibnr
 * @property string  $rilIdentifier
 * @property string  $name
 * @property double  $latitude
 * @property double  $longitude
 * @property int     $time_offset
 * @property bool    $shift_time
 * @property Event[] $events
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 */
class TrainStation extends Model
{

    use HasFactory;

    protected $fillable = ['ibnr', 'rilIdentifier', 'name', 'latitude', 'longitude', 'time_offset', 'shift_time'];
    protected $hidden   = ['created_at', 'updated_at', 'time_offset', 'shift_time'];
    protected $casts    = [
        'id'        => 'integer',
        'ibnr'      => 'integer',
        'latitude'  => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    public function events(): HasMany {
        return $this->hasMany(Event::class, 'trainstation', 'id');
    }
}
