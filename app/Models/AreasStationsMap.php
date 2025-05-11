<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string       $id
 * @property string       $station_id
 * @property string       $area_id
 * @property bool         $default Whether it's the default area for the station
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 * @property-read Area    $area
 * @property-read Station $station
 * @method static Builder<static>|AreasStationsMap newModelQuery()
 * @method static Builder<static>|AreasStationsMap newQuery()
 * @method static Builder<static>|AreasStationsMap query()
 * @method static Builder<static>|AreasStationsMap whereAreaId($value)
 * @method static Builder<static>|AreasStationsMap whereCreatedAt($value)
 * @method static Builder<static>|AreasStationsMap whereDefault($value)
 * @method static Builder<static>|AreasStationsMap whereId($value)
 * @method static Builder<static>|AreasStationsMap whereStationId($value)
 * @method static Builder<static>|AreasStationsMap whereUpdatedAt($value)
 * @mixin Eloquent
 */
class AreasStationsMap extends Pivot
{
    use HasUuids;

    protected $table    = 'areas_stations_maps';
    protected $fillable = ['id', 'station_id', 'area_id', 'default'];
    protected $casts    = [
        'id'         => 'string',
        'station_id' => 'string',
        'area_id'    => 'string',
        'default'    => 'boolean',
    ];

    public function area(): BelongsTo {
        return $this->belongsTo(Area::class);
    }

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class);
    }
}
