<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string                                 $id
 * @property string                                 $type
 * @property string|null                            $url
 * @property Carbon                                 $active_from
 * @property Carbon|null                            $active_until
 * @property Carbon|null                            $created_at
 * @property Carbon|null                            $updated_at
 * @property-read Collection<int, AlertTranslation> $translations
 * @property-read int|null                          $translations_count
 * @method static Builder<static>|Alert newModelQuery()
 * @method static Builder<static>|Alert newQuery()
 * @method static Builder<static>|Alert query()
 * @method static Builder<static>|Alert whereActiveFrom($value)
 * @method static Builder<static>|Alert whereActiveUntil($value)
 * @method static Builder<static>|Alert whereCreatedAt($value)
 * @method static Builder<static>|Alert whereId($value)
 * @method static Builder<static>|Alert whereType($value)
 * @method static Builder<static>|Alert whereUpdatedAt($value)
 * @method static Builder<static>|Alert whereUrl($value)
 * @mixin Eloquent
 */
class Alert extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'type',
        'active_from',
        'active_until'
    ];

    protected $casts = [
        'active_from'  => 'datetime',
        'active_until' => 'datetime',
    ];

    public function translations(): HasMany {
        return $this->hasMany(AlertTranslation::class, 'alert_id', 'id');
    }
}
