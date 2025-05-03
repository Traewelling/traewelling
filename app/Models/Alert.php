<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string                  $id
 * @property string                  $type
 * @property string|null             $url
 * @property Carbon                  $active_from
 * @property Carbon                  $active_until
 * @property Carbon                  $created_at
 * @property Carbon                  $updated_at
 *
 * @property-read AlertTranslation[] $translations
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
