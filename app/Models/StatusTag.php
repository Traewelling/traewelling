<?php

namespace App\Models;

use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $status_id
 * @property string $key
 * @property string $value
 * @property StatusVisibility $visibility
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Enum\StatusTagKey|null $key_enum
 * @property-read \App\Models\Status $status
 *
 * @method static \Database\Factories\StatusTagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusTag whereVisibility($value)
 *
 * @mixin \Eloquent
 */
class StatusTag extends Model
{
    use HasFactory;

    protected $fillable = ['status_id', 'key', 'value', 'visibility'];

    protected $appends = ['keyEnum'];

    protected $casts = [
        'status_id' => 'integer',
        'key' => 'string',
        'value' => 'string',
        'visibility' => StatusVisibility::class,
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function getKeyEnumAttribute(): ?StatusTagKey
    {
        return StatusTagKey::tryFrom($this->key);
    }
}
