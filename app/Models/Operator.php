<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $wikidata_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OperatorIdentifier> $identifiers
 * @property-read int|null $identifiers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 *
 * @method static \Database\Factories\OperatorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Operator whereWikidataId($value)
 *
 * @mixin \Eloquent
 */
class Operator extends Model
{
    use HasFactory;

    protected $table = 'hafas_operators'; // todo: rename table & foreign keys in database

    protected $fillable = ['wikidata_id', 'name'];

    protected $casts = [
        'id' => 'integer',
        'wikidata_id' => 'string',
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
