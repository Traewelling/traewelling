<?php

namespace App\Models;

use App\Services\PolylineStorageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $hash
 * @property string $polyline
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $source
 * @property int|null $parent_id
 * @property-read PolyLine|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 *
 * @method static \Database\Factories\PolyLineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine wherePolyline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PolyLine whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PolyLine extends Model
{
    use HasFactory;

    private PolylineStorageService $polylineStorageService;

    protected $fillable = ['hash', 'polyline', 'source', 'parent_id'];

    protected $casts = [
        'id' => 'integer',
        'source' => 'string',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->polylineStorageService = new PolylineStorageService();
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'polyline_id', 'id');
    }

    public function parent(): HasOne
    {
        return $this->hasOne(PolyLine::class, 'parent_id', 'id');
    }

    public function __get($key)
    {
        // check if the polyline is empty
        if ($key === 'polyline') {
            return $this->polylineStorageService->getOrCreate($this);
        }

        return parent::__get($key);
    }

    public function delete(): ?bool
    {
        $this->polylineStorageService->delete($this->hash);

        return parent::delete();
    }
}
