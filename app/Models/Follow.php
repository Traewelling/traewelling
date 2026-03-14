<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $follow_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $following
 * @property-read User|null $user
 *
 * @method static \Database\Factories\FollowFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereFollowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Follow whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Follow extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'follow_id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'follow_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function following(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_id', 'id');
    }
}
