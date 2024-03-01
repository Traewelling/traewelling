<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int     $id
 * @property int     $ibnr
 * @property string  $rilIdentifier
 * @property string  $name
 * @property double  $latitude
 * @property double  $longitude
 * @property int     $time_offset
 * @property bool    $shift_time
 * @property Event[] $events
 * @property Carbon  $created_at
 * @property Carbon  $updated_at
 * @todo rename table to "Station" (without Train - we have more than just trains)
 */
class Station extends Model
{

    use HasFactory, LogsActivity;

    protected $table    = 'train_stations';
    protected $fillable = ['ibnr', 'wikidata_id', 'rilIdentifier', 'name', 'latitude', 'longitude', 'time_offset', 'shift_time'];
    protected $hidden   = ['created_at', 'updated_at', 'time_offset', 'shift_time'];
    protected $casts    = [
        'id'            => 'integer',
        'rilIdentifier' => 'string',
        'name'          => 'string',
        'ibnr'          => 'integer',
        'wikidata_id'   => 'string',
        'latitude'      => 'float',
        'longitude'     => 'float',
    ];

    public function wikidataEntity(): BelongsTo {
        return $this->belongsTo(WikidataEntity::class, 'wikidata_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->dontSubmitEmptyLogs()
                         ->logOnlyDirty();
    }
}
