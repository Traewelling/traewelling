<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string                    $id
 * @property string                    $name
 * @property int                       $adminLevel
 * @property Carbon                    $created_at
 * @property Carbon                    $updated_at
 *
 * relations
 * @property-read Collection|Station[] $stations
 */
class Area extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'name', 'adminLevel'];
    protected $casts    = [
        'id'         => 'string',
        'name'       => 'string',
        'adminLevel' => 'integer',
    ];

    public function stations(): BelongsToMany {
        return $this->belongsToMany(Station::class, 'areas_stations_maps')
                    ->withPivot(['default'])
                    ->using(AreasStationsMap::class);
    }
}
