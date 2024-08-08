<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * // properties
 * @property int         $id
 * @property int|null    $ibnr
 * @property string      $rilIdentifier
 * @property string      $name
 * @property double      $latitude
 * @property double      $longitude
 * @property int         $time_offset
 * @property bool        $shift_time
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 *
 * // relations
 * @property Collection  $names
 *
 * // appends
 * @property string|null $ifopt
 * @property string|null $localized_name
 *
 *
 * @todo rename table to "Station" (without Train - we have more than just trains)
 */
class Station extends Model
{

    use HasFactory, LogsActivity;

    protected $table    = 'train_stations';
    protected $fillable = [
        'ibnr', 'wikidata_id', 'rilIdentifier',
        'ifopt_a', 'ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e',
        'name', 'latitude', 'longitude', 'time_offset', 'shift_time'
    ];
    protected $hidden   = ['created_at', 'updated_at', 'time_offset', 'shift_time'];
    protected $casts    = [
        'id'            => 'integer',
        'ibnr'          => 'integer',
        'wikidata_id'   => 'string',
        'ifopt_a'       => 'string',
        'ifopt_b'       => 'integer',
        'ifopt_c'       => 'integer',
        'ifopt_d'       => 'integer',
        'ifopt_e'       => 'integer',
        'rilIdentifier' => 'string',
        'name'          => 'string',
        'latitude'      => 'float',
        'longitude'     => 'float',
    ];
    protected $appends  = ['ifopt', 'localized_name'];

    public function names(): HasMany {
        return $this->hasMany(StationName::class, 'station_id', 'id');
    }

    public function getIfoptAttribute(): ?string {
        if (!$this->ifopt_a) {
            return null;
        }
        $ifopt = $this->ifopt_a;
        foreach (['b', 'c', 'd', 'e'] as $level) {
            if ($this->{"ifopt_$level"}) {
                $ifopt .= ':' . $this->{"ifopt_$level"};
            }
        }
        return $ifopt;
    }

    public function getLocalizedNameAttribute(): ?string {
        return $this->names->where('language', app()->getLocale())->first()?->name ?? $this->name;
    }

    public function getActivitylogOptions(): LogOptions {
        return LogOptions::defaults()
                         ->dontSubmitEmptyLogs()
                         ->logOnlyDirty();
    }
}
