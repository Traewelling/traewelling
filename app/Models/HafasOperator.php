<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @todo rename table only to "Operator" (or "TransportOperator", ..., but not HAFAS)
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
