<?php

namespace App\Models;

use App\Enum\ProfileLinkName;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property string $id
 * @property int $user_id
 * @property ProfileLinkName $name
 * @property string $url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static Builder<static>|ProfileLink newModelQuery()
 * @method static Builder<static>|ProfileLink newQuery()
 * @method static Builder<static>|ProfileLink query()
 * @method static Builder<static>|ProfileLink whereCreatedAt($value)
 * @method static Builder<static>|ProfileLink whereId($value)
 * @method static Builder<static>|ProfileLink whereName($value)
 * @method static Builder<static>|ProfileLink whereUpdatedAt($value)
 * @method static Builder<static>|ProfileLink whereUrl($value)
 * @method static Builder<static>|ProfileLink whereUserId($value)
 * @mixin Eloquent
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
        'name'       => ProfileLinkName::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
