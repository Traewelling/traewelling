<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $muted_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $mutedUser
 * @property-read User $user
 *
 * @method static \Database\Factories\UserMuteFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereMutedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserMute whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserMute extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'muted_id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'muted_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mutedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muted_id', 'id');
    }
}
