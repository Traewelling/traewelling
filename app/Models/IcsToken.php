<?php

namespace App\Models;

use Database\Factories\IcsTokenFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int         $id
 * @property int         $user_id
 * @property string|null $name
 * @property string      $token
 * @property Carbon|null $last_accessed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User   $user
 * @method static IcsTokenFactory factory($count = null, $state = [])
 * @method static Builder<static>|IcsToken newModelQuery()
 * @method static Builder<static>|IcsToken newQuery()
 * @method static Builder<static>|IcsToken query()
 * @method static Builder<static>|IcsToken whereCreatedAt($value)
 * @method static Builder<static>|IcsToken whereId($value)
 * @method static Builder<static>|IcsToken whereLastAccessed($value)
 * @method static Builder<static>|IcsToken whereName($value)
 * @method static Builder<static>|IcsToken whereToken($value)
 * @method static Builder<static>|IcsToken whereUpdatedAt($value)
 * @method static Builder<static>|IcsToken whereUserId($value)
 * @mixin Eloquent
 */
class IcsToken extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'token', 'last_accessed'];
    protected $casts    = [
        'id'            => 'integer',
        'user_id'       => 'integer',
        'last_accessed' => 'datetime',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
