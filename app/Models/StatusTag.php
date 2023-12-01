<?php

namespace App\Models;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusTag extends Model
{
    use HasFactory;

    protected $fillable = ['status_id', 'key', 'value', 'visibility'];
    protected $appends  = ['keyEnum'];
    protected $casts    = [
        'status_id'  => 'integer',
        'key'        => 'string',
        'value'      => 'string',
        'visibility' => StatusVisibility::class,
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function getKeyEnumAttribute(): ?StatusTagKey {
        return StatusTagKey::tryFrom($this->key);
    }
}
