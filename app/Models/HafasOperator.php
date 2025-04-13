<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $name
 * @property string $hafas_id
 * @property int    $motis_id
 * @property string $motis_source
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @todo rename table only to "Operator" (or "TransportOperator", ..., but not HAFAS)
 */
class HafasOperator extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'hafas_id', 'motis_id', 'motis_source'];
    protected $casts    = [
        'id'           => 'integer',
        'name'         => 'string',
        'hafas_id'     => 'string',
        'motis_id'     => 'integer',
        'motis_source' => 'string',
    ];

    public function trips(): HasMany {
        return $this->hasMany(Trip::class, 'operator_id', 'id');
    }
}
