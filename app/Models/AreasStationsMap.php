<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $id
 * @property string $station_id
 * @property string $area_id
 * @property bool $default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Area $area
 * @property-read \App\Models\Station $station
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AreasStationsMap whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AreasStationsMap extends Pivot
{
    use HasUuids;

    protected $table = 'areas_stations_maps';

    protected $fillable = ['id', 'station_id', 'area_id', 'default'];

    protected $casts = [
        'id' => 'string',
        'station_id' => 'string',
        'area_id' => 'string',
        'default' => 'boolean',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
