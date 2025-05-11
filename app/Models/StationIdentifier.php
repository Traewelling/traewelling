<?php

namespace App\Models;

use Database\Factories\StationIdentifierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string       $id
 * @property int          $relevance
 * @property int          $station_id
 * @property string       $type
 * @property string|null  $origin
 * @property string       $identifier
 * @property string|null  $name Name of the station provided by the data source
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 * @property-read Station $station
 * @method static StationIdentifierFactory factory($count = null, $state = [])
 * @method static Builder<static>|StationIdentifier newModelQuery()
 * @method static Builder<static>|StationIdentifier newQuery()
 * @method static Builder<static>|StationIdentifier query()
 * @method static Builder<static>|StationIdentifier whereCreatedAt($value)
 * @method static Builder<static>|StationIdentifier whereId($value)
 * @method static Builder<static>|StationIdentifier whereIdentifier($value)
 * @method static Builder<static>|StationIdentifier whereName($value)
 * @method static Builder<static>|StationIdentifier whereOrigin($value)
 * @method static Builder<static>|StationIdentifier whereRelevance($value)
 * @method static Builder<static>|StationIdentifier whereStationId($value)
 * @method static Builder<static>|StationIdentifier whereType($value)
 * @method static Builder<static>|StationIdentifier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StationIdentifier extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = ['relevance', 'station_id', 'identifier', 'type', 'origin', 'name'];
    protected $visible  = [
        'station_id',
        'identifier',
        'type',
        'origin',
        // Relations
        'station',
        'relevance',
    ];

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class);
    }

    public function getRawTransitousApiLinkToDepartures(): string {
        $params = [
            'stopId' => $this->identifier,
            'radius' => config('trwl.motis.radius'),
            'n'      => config('trwl.motis.results'),
        ];
        return 'https://api.transitous.org/api/v1/stoptimes?' . http_build_query($params);
    }
}
