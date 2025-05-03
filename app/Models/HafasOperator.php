<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read  int    $id
 * @property string       $name
 * @property ?string      $hafas_id
 * @property ?int         $motis_id
 * @property ?string      $motis_source
 * @property-read  Carbon $created_at
 * @property-read  Carbon $updated_at
 *
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
