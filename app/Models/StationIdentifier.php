<?php

namespace App\Models;

use App\Enum\StationIdentifierType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use phpGPX\Models\Point;

/**
 * @property string $id
 * @property int $station_id
 * @property StationIdentifierType $type
 * @property string|null $origin
 * @property string $identifier
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $relevance
 * @property float|null $latitude
 * @property float|null $longitude
 * @property-read Point $location
 * @property-read Station $station
 *
 * @method static \Database\Factories\StationIdentifierFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereRelevance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationIdentifier whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class StationIdentifier extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['relevance', 'station_id', 'identifier', 'type', 'origin', 'name', 'latitude', 'longitude'];

    protected $visible = [
        'station_id',
        'identifier',
        'type',
        'origin',
        'latitude',
        'longitude',
        // Relations
        'station',
        'relevance',
    ];

    protected $casts = [
        'type' => StationIdentifierType::class,
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function getLocationAttribute(): Point
    {
        $point = new Point(Point::TRACKPOINT);
        $point->latitude = $this->latitude;
        $point->longitude = $this->longitude;

        return $point;
    }

    public function getRawTransitousApiLinkToDepartures(): string
    {
        $params = [
            'stopId' => $this->identifier,
            'radius' => config('trwl.motis.radius'),
            'n' => config('trwl.motis.results'),
        ];

        return 'https://api.transitous.org/api/v6/stoptimes?' . http_build_query($params);
    }
}
