<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $follow_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $requestedFollow
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereFollowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FollowRequest whereUserId($value)
 *
 * @mixin \Eloquent
 */
class FollowRequest extends Model
{
    protected $fillable = ['user_id', 'follow_id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'follow_id' => 'integer',
    ];

    /**
     * @return BelongsTo The user who initiated the request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return BelongsTo The user who has to accept or deny the Request
     */
    public function requestedFollow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_id', 'id');
    }
}
