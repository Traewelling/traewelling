<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $last_accessed
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\IcsTokenFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereLastAccessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcsToken whereUserId($value)
 *
 * @mixin \Eloquent
 */
class IcsToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'token', 'last_accessed'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'last_accessed' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
