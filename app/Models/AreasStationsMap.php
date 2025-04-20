<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string       $id
 * @property string       $station_id
 * @property string       $area_id
 * @property bool         $default
 *
 * @property-read Area    $area
 * @property-read Station $station
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
