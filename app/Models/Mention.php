<?php

namespace App\Models;

use App\Dto\MentionDto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * //properties
 * @property int    id
 * @property int    status_id
 * @property int    mentioned_id
 * @property int    position
 * @property int    length
 *
 * //relations
 * @property Status status
 * @property User   mentioned
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
