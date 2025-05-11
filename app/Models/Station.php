<?php

namespace App\Models;

use Database\Factories\StationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 
 *
 * @todo rename table to "Station" (without Train - we have more than just trains)
 * @property int $id
 * @property int|null $ibnr
 * @property string|null $wikidata_id
 * @property string|null $ifopt_a Country
 * @property int|null $ifopt_b Administrative Area
 * @property int|null $ifopt_c Mode or Stop Place
 * @property int|null $ifopt_d Stop Place or Stop Place Component
 * @property int|null $ifopt_e Stop Place Component (or unused)
 * @property string|null $rilIdentifier
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 * @property string|null $source
 * @property int $relevance
 * @property int|null $time_offset Defines the offset of the train station relative to Europe/Berlin
 * @property int|null $shift_time If false, the timezone of the hafas request will not be shifted to Europe/Berlin
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EloquentCollection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\AreasStationsMap|null $pivot
 * @property-read EloquentCollection<int, \App\Models\Area> $areas
 * @property-read int|null $areas_count
 * @property-read string|null $ifopt
 * @property-read string|null $localized_name
 * @property-read EloquentCollection<int, \App\Models\StationName> $names
 * @property-read int|null $names_count
 * @property-read EloquentCollection<int, \App\Models\StationIdentifier> $stationIdentifiers
 * @property-read int|null $station_identifiers_count
 * @property-read EloquentCollection<int, \App\Models\Stopover> $stopovers
 * @property-read int|null $stopovers_count
 * @method static \Database\Factories\StationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Station newModelQuery()
 * @method static Builder<static>|Station newQuery()
 * @method static Builder<static>|Station query()
 * @method static Builder<static>|Station whereCreatedAt($value)
 * @method static Builder<static>|Station whereIbnr($value)
 * @method static Builder<static>|Station whereId($value)
 * @method static Builder<static>|Station whereIfoptA($value)
 * @method static Builder<static>|Station whereIfoptB($value)
 * @method static Builder<static>|Station whereIfoptC($value)
 * @method static Builder<static>|Station whereIfoptD($value)
 * @method static Builder<static>|Station whereIfoptE($value)
 * @method static Builder<static>|Station whereLatitude($value)
 * @method static Builder<static>|Station whereLongitude($value)
 * @method static Builder<static>|Station whereName($value)
 * @method static Builder<static>|Station whereRelevance($value)
 * @method static Builder<static>|Station whereRilIdentifier($value)
 * @method static Builder<static>|Station whereShiftTime($value)
 * @method static Builder<static>|Station whereSource($value)
 * @method static Builder<static>|Station whereTimeOffset($value)
 * @method static Builder<static>|Station whereUpdatedAt($value)
 * @method static Builder<static>|Station whereWikidataId($value)
 * @mixin \Eloquent
 */
class Station extends Model
{

    use HasFactory, LogsActivity;

    protected $table    = 'train_stations';
    protected $fillable = [
        'ibnr', 'wikidata_id', 'rilIdentifier',
        'ifopt_a', 'ifopt_b', 'ifopt_c', 'ifopt_d', 'ifopt_e', 'relevance',
        'name', 'latitude', 'longitude', 'source', 'time_offset', 'shift_time'
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

    public function stationIdentifiers(): HasMany {
        return $this->hasMany(StationIdentifier::class, 'station_id', 'id');
    }

    public function stopovers(): HasMany {
        return $this->hasMany(Stopover::class, 'station_id', 'id');
    }

    public function areas(): BelongsToMany {
        return $this->belongsToMany(Area::class, 'areas_stations_maps')
                    ->withPivot('default')
                    ->using(AreasStationsMap::class);
    }
}
