<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $operator_id
 * @property string $type
 * @property string $identifier
 * @property string|null $source
 * @property string|null $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Operator $operator
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereOperatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OperatorIdentifier whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class OperatorIdentifier extends Model
{
    use HasUuids;

    protected $fillable = [
        'operator_id',
        'identifier',
        'type',
        'source',
        'name',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}
