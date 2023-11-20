<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int             $id
 * @property string          $trip_id
 * @property HafasTravelType $category
 * @property string          $number
 * @property string          $linename
 * @property string          $journey_number
 * @property int             $operator_id
 * @property int             $origin
 * @property int             $destination
 * @property int             $polyline_id
 * @property UTCDateTime     $departure
 * @property UTCDateTime     $arrival
 * @property UTCDateTime     $last_refreshed
 * @property TripSource      $source
 * @property                 $stopovers
 * @property PolyLine        $polyLine
 *
 * @todo rename table only to "Trip" (without Hafas)
 * @todo rename "linename" to "line_name" (or something else, but not "linename")
 * @todo migrate origin & destination to use "id" instead of "ibnr" and rename to "origin_id" & "destination_id"
 * @todo is "delay" still needed? We save planned and real in the stopovers. check.
 */
class HafasTrip extends Model
{

    use HasFactory;

    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'journey_number', 'operator_id', 'origin', 'destination',
        'polyline_id', 'departure', 'arrival', 'delay', 'source', 'last_refreshed',
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
        'source'         => TripSource::class,
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

    public function checkins(): HasMany {
        return $this->hasMany(TrainCheckin::class, 'trip_id', 'trip_id');
    }
}
