<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use App\Enum\HafasTravelType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property $stopovers
 * @property PolyLine $polyLine
 * @property PolyLine $polyline
 */
class HafasTrip extends Model
{

    use HasFactory;

    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'journey_number', 'operator_id', 'origin', 'destination',
        'polyline_id', 'departure', 'arrival', 'delay', 'last_refreshed',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $casts    = [
        'id'             => 'integer',
        'trip_id'        => 'string',
        'category'       => HafasTravelType::class,
        'journey_number' => 'integer',
        'operator_id'    => 'integer',
        'origin'         => 'integer',
        'destination'    => 'integer',
        'polyline_id'    => 'integer',
        'departure'      => UTCDateTime::class,
        'arrival'        => UTCDateTime::class,
        'last_refreshed' => 'datetime',
    ];

    public function polyline(): HasOne {
        return $this->hasOne(PolyLine::class, 'id', 'polyline_id');
    }

    public function originStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'origin', 'ibnr');
    }

    public function destinationStation(): BelongsTo {
        return $this->belongsTo(TrainStation::class, 'destination', 'ibnr');
    }

    public function operator(): BelongsTo {
        return $this->belongsTo(HafasOperator::class, 'operator_id', 'id');
    }

    public function stopovers(): HasMany {
        return $this->hasMany(TrainStopover::class, 'trip_id', 'trip_id')
                    ->orderBy('arrival_planned')
                    ->orderBy('departure_planned');
    }

    public function checkIns(): HasMany {
        return $this->hasMany(TrainCheckin::class, 'trip_id', 'trip_id');
    }
}
