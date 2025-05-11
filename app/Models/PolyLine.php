<?php

namespace App\Models;

use App\Services\PolylineStorageService;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int                        $id
 * @property int|null                   $parent_id
 * @property string                     $hash
 * @property string                     $polyline
 * @property string                     $source
 * @property Carbon|null                $created_at
 * @property Carbon|null                $updated_at
 * @property-read PolyLine|null         $parent
 * @property-read Collection<int, Trip> $trips
 * @property-read int|null              $trips_count
 * @method static Builder<static>|PolyLine newModelQuery()
 * @method static Builder<static>|PolyLine newQuery()
 * @method static Builder<static>|PolyLine query()
 * @method static Builder<static>|PolyLine whereCreatedAt($value)
 * @method static Builder<static>|PolyLine whereHash($value)
 * @method static Builder<static>|PolyLine whereId($value)
 * @method static Builder<static>|PolyLine whereParentId($value)
 * @method static Builder<static>|PolyLine wherePolyline($value)
 * @method static Builder<static>|PolyLine whereSource($value)
 * @method static Builder<static>|PolyLine whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PolyLine extends Model
{
    private PolylineStorageService $polylineStorageService;
    protected                      $fillable = ['hash', 'polyline', 'source', 'parent_id'];
    protected                      $casts    = [
        'id'     => 'integer',
        'source' => 'string', //enum['hafas', 'brouter'] in database
    ];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->polylineStorageService = new PolylineStorageService();
    }

    public function trips(): HasMany {
        return $this->hasMany(Trip::class, 'polyline_id', 'id');
    }

    public function parent(): HasOne {
        return $this->hasOne(PolyLine::class, 'parent_id', 'id');
    }

    public function __get($key) {
        // check if the polyline is empty
        if ($key === 'polyline') {
            return $this->polylineStorageService->getOrCreate($this);
        }

        return parent::__get($key);
    }

    public function delete(): ?bool {
        $this->polylineStorageService->delete($this->hash);
        return parent::delete();
    }
}
