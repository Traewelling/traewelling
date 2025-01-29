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
 * @property int             $origin_id
 * @property int             $destination_id
 * @property int             $polyline_id
 * @property UTCDateTime     $departure
 * @property UTCDateTime     $arrival
 * @property UTCDateTime     $last_refreshed
 * @property TripSource      $source
 * @property int             $user_id
 * @property                 $stopovers
 * @property PolyLine        $polyLine
 *
 * @todo rename table only to "Trip" (without Hafas)
 * @todo rename "linename" to "line_name" (or something else, but not "linename")
 * @todo drop origin and destination, when origin_id and destination_id are added
 */
class Trip extends Model
{

    use HasFactory;

    protected $table    = 'hafas_trips';
    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'journey_number', 'operator_id', 'origin_id', 'destination_id',
        'polyline_id', 'departure', 'arrival', 'source', 'user_id', 'last_refreshed',
    ];
    protected $hidden   = ['created_at', 'updated_at'];
    protected $casts    = [
        'id'             => 'integer',
        'trip_id'        => 'string',
        'number'         => 'string',
        'category'       => HafasTravelType::class,
        'journey_number' => 'integer',
        'operator_id'    => 'integer',
        'origin_id'      => 'integer',
        'destination_id' => 'integer',
        'polyline_id'    => 'integer',
        'departure'      => UTCDateTime::class,
        'arrival'        => UTCDateTime::class,
        'last_refreshed' => 'datetime',
        'source'         => TripSource::class,
        'user_id'        => 'integer',
    ];

    public function polyline(): HasOne {
        return $this->hasOne(PolyLine::class, 'id', 'polyline_id');
    }

    public function originStation(): BelongsTo {
        return $this->belongsTo(Station::class, 'origin_id', 'id');
    }

    public function destinationStation(): BelongsTo {
        return $this->belongsTo(Station::class, 'destination_id', 'id');
    }

    public function operator(): BelongsTo {
        return $this->belongsTo(HafasOperator::class, 'operator_id', 'id');
    }

    public function stopovers(): HasMany {
        return $this->hasMany(Stopover::class, 'trip_id', 'trip_id')
                    ->orderBy('arrival_planned')
                    ->orderBy('departure_planned');
    }

    public function checkins(): HasMany {
        return $this->hasMany(Checkin::class, 'trip_id', 'trip_id');
    }

    /**
     * If this trip was created by a user, this model belongs to the user, so they can edit and delete it.
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
