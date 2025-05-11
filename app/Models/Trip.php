<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use App\Enum\HafasTravelType;
use App\Enum\TripSource;
use Database\Factories\TripFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @todo rename table only to "Trip" (without Hafas)
 * @todo rename "linename" to "line_name" (or something else, but not "linename")
 * @todo drop origin and destination, when origin_id and destination_id are added
 * @property int                            $id
 * @property string                         $trip_id
 * @property HafasTravelType                $category
 * @property string                         $number
 * @property string                         $linename
 * @property int|null                       $journey_number
 * @property int|null                       $operator_id
 * @property int                            $origin_id
 * @property int                            $destination_id
 * @property int|null                       $polyline_id
 * @property $departure
 * @property $arrival
 * @property TripSource                     $source
 * @property string|null                    $motis_source
 * @property string|null                    $motis_source_license_id
 * @property int|null                       $user_id if not null, this trip belongs to the user (e.g. manually created trips)
 * @property Carbon|null                    $last_refreshed
 * @property Carbon|null                    $created_at
 * @property Carbon|null                    $updated_at
 * @property-read Collection<int, Checkin>  $checkins
 * @property-read int|null                  $checkins_count
 * @property-read Station                   $destinationStation
 * @property-read MotisSourceLicense|null   $motisSourceLicense
 * @property-read HafasOperator|null        $operator
 * @property-read Station                   $originStation
 * @property-read PolyLine|null             $polyline
 * @property-read Collection<int, Stopover> $stopovers
 * @property-read int|null                  $stopovers_count
 * @property-read User|null                 $user
 * @method static TripFactory factory($count = null, $state = [])
 * @method static Builder<static>|Trip newModelQuery()
 * @method static Builder<static>|Trip newQuery()
 * @method static Builder<static>|Trip query()
 * @method static Builder<static>|Trip whereArrival($value)
 * @method static Builder<static>|Trip whereCategory($value)
 * @method static Builder<static>|Trip whereCreatedAt($value)
 * @method static Builder<static>|Trip whereDeparture($value)
 * @method static Builder<static>|Trip whereDestinationId($value)
 * @method static Builder<static>|Trip whereId($value)
 * @method static Builder<static>|Trip whereJourneyNumber($value)
 * @method static Builder<static>|Trip whereLastRefreshed($value)
 * @method static Builder<static>|Trip whereLinename($value)
 * @method static Builder<static>|Trip whereMotisSource($value)
 * @method static Builder<static>|Trip whereMotisSourceLicenseId($value)
 * @method static Builder<static>|Trip whereNumber($value)
 * @method static Builder<static>|Trip whereOperatorId($value)
 * @method static Builder<static>|Trip whereOriginId($value)
 * @method static Builder<static>|Trip wherePolylineId($value)
 * @method static Builder<static>|Trip whereSource($value)
 * @method static Builder<static>|Trip whereTripId($value)
 * @method static Builder<static>|Trip whereUpdatedAt($value)
 * @method static Builder<static>|Trip whereUserId($value)
 * @mixin \Eloquent
 */
class Trip extends Model
{

    use HasFactory;

    protected $table    = 'hafas_trips';
    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'journey_number', 'operator_id', 'origin_id', 'destination_id',
        'polyline_id', 'departure', 'arrival', 'source', 'motis_source', 'user_id', 'last_refreshed', 'motis_source_license_id'
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

    public function motisSourceLicense(): BelongsTo {
        return $this->belongsTo(MotisSourceLicense::class, 'motis_source_license_id', 'id');
    }
}
