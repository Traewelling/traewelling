<?php

namespace App\Models;

use App\Casts\UTCDateTime;
use App\Enum\HafasTravelType;
use App\Enum\MotisCategory;
use App\Enum\TripSource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @todo rename table only to "Trip" (without Hafas)
 * @todo rename "linename" to "line_name" (or something else, but not "linename")
 * @todo drop origin and destination, when origin_id and destination_id are added
 *
 * @property int $id
 * @property string|null $uuid
 * @property string $trip_id
 * @property HafasTravelType $category
 * @property string $number
 * @property string $linename
 * @property int|null $polyline_id
 * @property $departure
 * @property $arrival
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $operator_id
 * @property Carbon|null $last_refreshed
 * @property int|null $journey_number
 * @property TripSource $source
 * @property int|null $user_id
 * @property int $origin_id
 * @property int $destination_id
 * @property string|null $motis_source
 * @property string|null $motis_source_license_id
 * @property string|null $route_color
 * @property MotisCategory|null $mode
 * @property string|null $route_text_color
 * @property-read Collection<int, Checkin> $checkins
 * @property-read int|null $checkins_count
 * @property-read Station|null $destinationStation
 * @property-read MotisSourceLicense|null $motisSourceLicense
 * @property-read Operator|null $operator
 * @property-read Station|null $originStation
 * @property-read PolyLine|null $polyline
 * @property-read Collection<int, Stopover> $stopovers
 * @property-read int|null $stopovers_count
 * @property-read User|null $user
 *
 * @method static \Database\Factories\TripFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereJourneyNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLastRefreshed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereLinename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereMotisSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereMotisSourceLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereOriginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip wherePolylineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereRouteColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereRouteTextColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trip whereContinuationTripId($value)
 *
 * @mixin \Eloquent
 */
class Trip extends Model
{
    use HasFactory;

    protected $table = 'hafas_trips';

    protected static function boot(): void
    {
        parent::boot();

        // UUID is not yet the primary key, so we can't use the HasUuids trait here.
        static::creating(function (self $trip): void {
            if (empty($trip->uuid)) {
                $trip->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'trip_id', 'category', 'number', 'linename', 'route_color', 'route_text_color', 'journey_number', 'operator_id', 'origin_id',
        'destination_id', 'polyline_id', 'departure', 'arrival', 'source', 'motis_source', 'user_id', 'last_refreshed',
        'motis_source_license_id', 'mode',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'id' => 'integer',
        'uuid' => 'string',
        'trip_id' => 'string',
        'category' => HafasTravelType::class,
        'number' => 'string',
        'linename' => 'string',
        'route_color' => 'string',
        'route_text_color' => 'string',
        'journey_number' => 'integer',
        'operator_id' => 'string',
        'origin_id' => 'integer',
        'destination_id' => 'integer',
        'polyline_id' => 'integer',
        'departure' => UTCDateTime::class,
        'arrival' => UTCDateTime::class,
        'last_refreshed' => 'datetime',
        'source' => TripSource::class,
        'user_id' => 'integer',
        'mode' => MotisCategory::class,
    ];

    public function polyline(): HasOne
    {
        return $this->hasOne(PolyLine::class, 'id', 'polyline_id');
    }

    public function originStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'origin_id', 'id');
    }

    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'destination_id', 'id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'id');
    }

    public function stopovers(): HasMany
    {
        return $this->hasMany(Stopover::class, 'trip_id', 'trip_id')
            ->orderBy('arrival_planned')
            ->orderBy('departure_planned');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class, 'trip_id', 'trip_id');
    }

    /**
     * If this trip was created by a user, this model belongs to the user, so they can edit and delete it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function motisSourceLicense(): BelongsTo
    {
        return $this->belongsTo(MotisSourceLicense::class, 'motis_source_license_id', 'id');
    }
}
