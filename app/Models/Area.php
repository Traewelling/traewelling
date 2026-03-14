<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property int $adminLevel
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AreasStationsMap|null $pivot
 * @property-read Collection<int, Station> $stations
 * @property-read int|null $stations_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereAdminLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Area whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Area extends Model
{
    use HasUuids;

    protected $fillable = ['id', 'name', 'adminLevel'];

    protected $casts = [
        'id' => 'string',
        'name' => 'string',
        'adminLevel' => 'integer',
    ];

    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'areas_stations_maps')
            ->withPivot(['default'])
            ->using(AreasStationsMap::class);
    }
}
