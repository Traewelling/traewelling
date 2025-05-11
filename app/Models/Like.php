<?php

namespace App\Models;

use Database\Factories\LikeFactory;
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
 * @property int         $status_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Status $status
 * @property-read User   $user
 * @method static LikeFactory factory($count = null, $state = [])
 * @method static Builder<static>|Like newModelQuery()
 * @method static Builder<static>|Like newQuery()
 * @method static Builder<static>|Like query()
 * @method static Builder<static>|Like whereCreatedAt($value)
 * @method static Builder<static>|Like whereId($value)
 * @method static Builder<static>|Like whereStatusId($value)
 * @method static Builder<static>|Like whereUpdatedAt($value)
 * @method static Builder<static>|Like whereUserId($value)
 * @mixin Eloquent
 */
class Like extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'status_id'];
    protected $casts    = [
        'id'        => 'integer',
        'user_id'   => 'integer',
        'status_id' => 'integer',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

}
