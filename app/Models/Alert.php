<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
