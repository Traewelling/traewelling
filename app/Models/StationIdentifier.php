<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string  id
 * @property string  station_id
 * @property string  identifier
 * @property ?string name
 * @property string  type
 * @property string  origin
 * @property Station station
 */
class StationIdentifier extends Model
{
    use HasUuids;

    protected $fillable = ['station_id', 'identifier', 'type', 'origin', 'name'];
    protected $visible  = [
        'station_id',
        'identifier',
        'type',
        'origin',
        // Relations
        'station',
    ];

    public function station(): BelongsTo {
        return $this->belongsTo(Station::class);
    }
}
