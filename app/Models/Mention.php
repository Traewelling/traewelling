<?php

namespace App\Models;

use App\Dto\MentionDto;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int            $id
 * @property int            $status_id
 * @property int            $mentioned_id
 * @property int            $position
 * @property int            $length
 * @property Carbon|null    $created_at
 * @property Carbon|null    $updated_at
 * @property-read User|null $mentioned
 * @property-read Status    $status
 * @method static Builder<static>|Mention newModelQuery()
 * @method static Builder<static>|Mention newQuery()
 * @method static Builder<static>|Mention query()
 * @method static Builder<static>|Mention whereCreatedAt($value)
 * @method static Builder<static>|Mention whereId($value)
 * @method static Builder<static>|Mention whereLength($value)
 * @method static Builder<static>|Mention whereMentionedId($value)
 * @method static Builder<static>|Mention wherePosition($value)
 * @method static Builder<static>|Mention whereStatusId($value)
 * @method static Builder<static>|Mention whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Mention extends Model
{
    use HasFactory;

    protected $fillable = ['status_id', 'mentioned_id', 'position', 'length'];
    protected $casts    = [
        'status_id'    => 'int',
        'mentioned_id' => 'int',
        'position'     => 'int',
        'length'       => 'int',
    ];

    public function status(): BelongsTo {
        return $this->belongsTo(Status::class);
    }

    public function mentioned(): HasOne {
        return $this->hasOne(User::class, 'id', 'mentioned_id');
    }

    public static function fromMentionDto(MentionDto $mentionDto, Status $status): self {
        $mention               = new self();
        $mention->status_id    = $status->id;
        $mention->mentioned_id = $mentionDto->user->id;
        $mention->position     = $mentionDto->position;
        $mention->length       = $mentionDto->length;

        return $mention;
    }
}
