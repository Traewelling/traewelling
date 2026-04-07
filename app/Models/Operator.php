<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $legacy_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, OperatorIdentifier> $identifiers
 * @property-read int|null $identifiers_count
 * @property-read Collection<int, Trip> $trips
 * @property-read int|null $trips_count
 *
 * @method static \Database\Factories\OperatorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereLegacyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Operator extends Model
{
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name'];

    protected $casts = [
        'id' => 'string',
        'legacy_id' => 'integer',
        'name' => 'string',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'operator_id', 'id');
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(OperatorIdentifier::class, 'operator_id', 'id');
    }
}
