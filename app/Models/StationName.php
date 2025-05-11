<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string       $id
 * @property int          $station_id
 * @property string       $language
 * @property string       $name
 * @property Carbon|null  $created_at
 * @property Carbon|null  $updated_at
 * @property-read Station $station
 * @method static Builder<static>|StationName newModelQuery()
 * @method static Builder<static>|StationName newQuery()
 * @method static Builder<static>|StationName query()
 * @method static Builder<static>|StationName whereCreatedAt($value)
 * @method static Builder<static>|StationName whereId($value)
 * @method static Builder<static>|StationName whereLanguage($value)
 * @method static Builder<static>|StationName whereName($value)
 * @method static Builder<static>|StationName whereStationId($value)
 * @method static Builder<static>|StationName whereUpdatedAt($value)
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
