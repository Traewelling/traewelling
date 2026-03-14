<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $blocked_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $blockedUser
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereBlockedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBlock whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserBlock extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'blocked_id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'blocked_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id', 'id');
    }
}
