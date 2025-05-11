<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property string $id
 * @property int $station_id
 * @property string $language
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Station $station
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereStationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StationName whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StationName extends Model
{
    use HasUuids;

    protected $keyType      = 'string';
    public    $incrementing = false;
    protected $fillable     = ['station_id', 'language', 'name'];
    protected $casts        = [
        'station_id' => 'integer',
        'language'   => 'string',
        'name'       => 'string'
    ];

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
}
