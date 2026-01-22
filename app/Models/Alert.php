<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $type
 * @property string|null $url
 * @property \Illuminate\Support\Carbon $active_from
 * @property \Illuminate\Support\Carbon|null $active_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AlertTranslation> $translations
 * @property-read int|null $translations_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereActiveFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereActiveUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alert whereUrl($value)
 *
 * @mixin \Eloquent
 */
class Alert extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'type',
        'active_from',
        'active_until',
    ];

    protected $casts = [
        'active_from' => 'datetime',
        'active_until' => 'datetime',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(AlertTranslation::class, 'alert_id', 'id');
    }
}
