<?php

namespace App\Models;

use App\Enum\ProfileLinkName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $user_id
 * @property ProfileLinkName $name
 * @property string $url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileLink whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ProfileLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'url',
    ];

    protected $casts = [
        'name' => ProfileLinkName::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
