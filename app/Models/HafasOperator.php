<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @todo rename table only to "Operator" (or "TransportOperator", ..., but not HAFAS)
 * @property int $id
 * @property string|null $wikidata_id Wikidata ID of the operator
 * @property string|null $hafas_id
 * @property string $name
 * @property string|null $motis_id
 * @property string|null $motis_source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Trip> $trips
 * @property-read int|null $trips_count
 * @method static \Database\Factories\HafasOperatorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereHafasId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereMotisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereMotisSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HafasOperator whereWikidataId($value)
 * @mixin \Eloquent
 */
class HafasOperator extends Model
{
    use HasFactory;

    protected $fillable = ['wikidata_id', 'name', 'hafas_id', 'motis_id', 'motis_source'];
    protected $casts    = [
        'id'           => 'integer',
        'wikidata_id'  => 'string',
        'name'         => 'string',
        'hafas_id'     => 'string',
        'motis_id'     => 'string',
        'motis_source' => 'string',
    ];

    public function trips(): HasMany {
        return $this->hasMany(Trip::class, 'operator_id', 'id');
    }
}
