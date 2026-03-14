<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property int $trusted_id
 * @property Carbon|null $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $trusted
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereTrustedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TrustedUser whereUserId($value)
 *
 * @mixin \Eloquent
 */
class TrustedUser extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['user_id', 'trusted_id', 'expires_at'];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'integer',
        'trusted_id' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function trusted(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trusted_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
