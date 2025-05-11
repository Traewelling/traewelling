<?php

namespace App\Models;

use Database\Factories\UserMuteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $muted_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User $mutedUser
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserMuteFactory factory($count = null, $state = [])
 * @method static Builder<static>|UserMute newModelQuery()
 * @method static Builder<static>|UserMute newQuery()
 * @method static Builder<static>|UserMute query()
 * @method static Builder<static>|UserMute whereCreatedAt($value)
 * @method static Builder<static>|UserMute whereId($value)
 * @method static Builder<static>|UserMute whereMutedId($value)
 * @method static Builder<static>|UserMute whereUpdatedAt($value)
 * @method static Builder<static>|UserMute whereUserId($value)
 * @mixin \Eloquent
 */
class UserMute extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'muted_id'];
    protected $casts    = [
        'id'       => 'integer',
        'user_id'  => 'integer',
        'muted_id' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function mutedUser(): BelongsTo {
        return $this->belongsTo(User::class, 'muted_id', 'id');
    }
}
